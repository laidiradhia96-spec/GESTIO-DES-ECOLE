<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RevenueService
{
    /**
     * Résoudre le tarif actif d'un groupe à une date donnée.
     *
     * Cherche le GroupTariff dont la période couvre $date :
     *   effective_from <= date AND (effective_to IS NULL OR effective_to >= date)
     */
    public function resolveTariff(int $groupId, Carbon $date): ?GroupTariff
    {
        return GroupTariff::where('group_id', $groupId)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $date->toDateString())
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $date->toDateString());
            })
            ->latest('effective_from')
            ->first();
    }

    /**
     * Calculer les parts enseignant et académie à partir d'un montant payé et d'un tarif.
     *
     * @return array{teacher: float, academy: float}
     */
    public function calculateShares(float $amountPaid, GroupTariff $tariff): array
    {
        $studentPrice = (float) $tariff->student_price;

        if ($studentPrice <= 0) {
            return ['teacher' => 0.0, 'academy' => 0.0];
        }

        $teacherRate = (float) $tariff->teacher_share / $studentPrice;
        $academyRate = (float) $tariff->academy_share / $studentPrice;

        return [
            'teacher' => round($amountPaid * $teacherRate, 2),
            'academy' => round($amountPaid * $academyRate, 2),
        ];
    }

    /**
     * Revenus agrégés par enseignant pour la page principale.
     *
     * Filtres optionnels : mois, année, enseignant, groupe, matière, année scolaire.
     *
     * @return Collection<int, object{teacher: Teacher, payments_count: int, students_count: int, total_collected: float, teacher_share: float, academy_share: float}>
     */
    public function getTeacherRevenues(
        ?int $month = null,
        ?int $year = null,
        ?int $teacherId = null,
        ?int $groupId = null,
        ?int $subjectId = null,
        ?int $schoolYearId = null,
    ): Collection {
        $payments = $this->getFilteredPayments($month, $year, $teacherId, $groupId, $subjectId, $schoolYearId);

        $grouped = $payments->groupBy('group.teacher_id');

        $results = collect();

        foreach ($grouped as $teacherId => $teacherPayments) {
            $teacher = $teacherPayments->first()->group->teacher;

            $totalCollected = 0;
            $teacherShare = 0;
            $academyShare = 0;
            $studentIds = [];

            foreach ($teacherPayments as $payment) {
                // Utiliser le snapshot si disponible, sinon recalculer depuis le tarif actuel
                if ($payment->teacher_share !== null && $payment->academy_share !== null) {
                    $shares = [
                        'teacher' => (float) $payment->teacher_share,
                        'academy' => (float) $payment->academy_share,
                    ];
                } else {
                    $tariff = $this->resolveTariff($payment->group_id, $payment->payment_date);
                    $shares = $tariff
                        ? $this->calculateShares((float) $payment->amount_paid, $tariff)
                        : ['teacher' => 0.0, 'academy' => 0.0];
                }

                $totalCollected += (float) $payment->amount_paid;
                $teacherShare += $shares['teacher'];
                $academyShare += $shares['academy'];
                $studentIds[] = $payment->student_id;
            }

            $results->push((object) [
                'teacher' => $teacher,
                'payments_count' => $teacherPayments->count(),
                'students_count' => count(array_unique($studentIds)),
                'total_collected' => $totalCollected,
                'teacher_share' => $teacherShare,
                'academy_share' => $academyShare,
            ]);
        }

        return $results->sortByDesc('total_collected')->values();
    }

    /**
     * Liste détaillée des paiements pour la fiche d'un enseignant.
     *
     * @return Collection<int, object{payment: Payment, student: Student, group: Group, subject: Subject, tariff: GroupTariff|null, teacher_share: float, academy_share: float}>
     */
    public function getTeacherPayments(
        int $teacherId,
        ?int $month = null,
        ?int $year = null,
        ?int $schoolYearId = null,
    ): Collection {
        $payments = $this->getFilteredPayments($month, $year, $teacherId, null, null, $schoolYearId);

        $results = collect();

        foreach ($payments as $payment) {
            if ($payment->teacher_share !== null && $payment->academy_share !== null) {
                $shares = [
                    'teacher' => (float) $payment->teacher_share,
                    'academy' => (float) $payment->academy_share,
                ];
                $tariff = null;
            } else {
                $tariff = $this->resolveTariff($payment->group_id, $payment->payment_date);
                $shares = $tariff
                    ? $this->calculateShares((float) $payment->amount_paid, $tariff)
                    : ['teacher' => 0.0, 'academy' => 0.0];
            }

            $results->push((object) [
                'payment' => $payment,
                'student' => $payment->student,
                'group' => $payment->group,
                'subject' => $payment->subject,
                'tariff' => $tariff,
                'teacher_share' => $shares['teacher'],
                'academy_share' => $shares['academy'],
            ]);
        }

        return $results;
    }

    /**
     * Statistiques globales pour le dashboard de la page revenus.
     *
     * @return array{total_collected: float, total_teacher_share: float, total_academy_share: float, payments_count: int, teachers_count: int}
     */
    public function getRevenueStats(
        ?int $month = null,
        ?int $year = null,
        ?int $schoolYearId = null,
    ): array {
        $payments = $this->getFilteredPayments($month, $year, null, null, null, $schoolYearId);

        $totalCollected = 0;
        $totalTeacherShare = 0;
        $totalAcademyShare = 0;
        $teacherIds = [];

        foreach ($payments as $payment) {
            if ($payment->teacher_share !== null && $payment->academy_share !== null) {
                $shares = [
                    'teacher' => (float) $payment->teacher_share,
                    'academy' => (float) $payment->academy_share,
                ];
            } else {
                $tariff = $this->resolveTariff($payment->group_id, $payment->payment_date);
                $shares = $tariff
                    ? $this->calculateShares((float) $payment->amount_paid, $tariff)
                    : ['teacher' => 0.0, 'academy' => 0.0];
            }

            $totalCollected += (float) $payment->amount_paid;
            $totalTeacherShare += $shares['teacher'];
            $totalAcademyShare += $shares['academy'];
            $teacherIds[] = $payment->group->teacher_id;
        }

        return [
            'total_collected' => $totalCollected,
            'total_teacher_share' => $totalTeacherShare,
            'total_academy_share' => $totalAcademyShare,
            'payments_count' => $payments->count(),
            'teachers_count' => count(array_unique($teacherIds)),
        ];
    }

    /**
     * Revenus par groupe pour la fiche individuelle d'un enseignant.
     *
     * Agrège les paiements réels par group_id pour un enseignant donné.
     * Utilise les snapshots historiques (teacher_share / academy_share) stockés dans chaque paiement.
     *
     * @return Collection<int, object{group: Group, subject: Subject, total_collected: float, teacher_share: float, academy_share: float, payments_count: int}>
     */
    public function getTeacherRevenueByGroup(
        int $teacherId,
        ?int $month = null,
        ?int $year = null,
        ?int $schoolYearId = null,
    ): Collection {
        $payments = $this->getFilteredPayments($month, $year, $teacherId, null, null, $schoolYearId);

        $grouped = $payments->groupBy('group_id');

        $results = collect();

        foreach ($grouped as $groupId => $groupPayments) {
            $group = $groupPayments->first()->group;
            $subject = $groupPayments->first()->subject;

            $totalCollected = 0;
            $teacherShare = 0;
            $academyShare = 0;

            foreach ($groupPayments as $payment) {
                if ($payment->teacher_share !== null && $payment->academy_share !== null) {
                    $shares = [
                        'teacher' => (float) $payment->teacher_share,
                        'academy' => (float) $payment->academy_share,
                    ];
                } else {
                    $tariff = $this->resolveTariff($payment->group_id, $payment->payment_date);
                    $shares = $tariff
                        ? $this->calculateShares((float) $payment->amount_paid, $tariff)
                        : ['teacher' => 0.0, 'academy' => 0.0];
                }

                $totalCollected += (float) $payment->amount_paid;
                $teacherShare += $shares['teacher'];
                $academyShare += $shares['academy'];
            }

            $results->push((object) [
                'group' => $group,
                'subject' => $subject,
                'total_collected' => $totalCollected,
                'teacher_share' => $teacherShare,
                'academy_share' => $academyShare,
                'payments_count' => $groupPayments->count(),
            ]);
        }

        return $results->sortBy('group.name')->values();
    }

    /**
     * Récupérer les paiements filtrés avec eager loading optimisé.
     */
    private function getFilteredPayments(
        ?int $month = null,
        ?int $year = null,
        ?int $teacherId = null,
        ?int $groupId = null,
        ?int $subjectId = null,
        ?int $schoolYearId = null,
    ): Collection {
        $query = Payment::with(['student', 'group.teacher', 'subject'])
            ->whereNotNull('group_id');

        // Filtre par mois/année via payment_date
        if ($month && $year) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $query->whereDate('payment_date', '>=', $startDate->toDateString())
                ->whereDate('payment_date', '<=', $endDate->toDateString());
        } elseif ($year) {
            $query->whereYear('payment_date', $year);
        }

        // Filtre par enseignant (via groupe)
        if ($teacherId) {
            $query->whereHas('group', fn ($q) => $q->where('teacher_id', $teacherId));
        }

        // Filtre par groupe
        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        // Filtre par matière
        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        // Filtre par année scolaire
        if ($schoolYearId) {
            $query->where('school_year_id', $schoolYearId);
        }

        return $query->orderBy('payment_date', 'desc')->get();
    }
}
