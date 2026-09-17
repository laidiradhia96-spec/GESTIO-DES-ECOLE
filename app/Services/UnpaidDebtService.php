<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\Payment;
use App\Models\PaymentSignalement;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Calcul READ ONLY de la liste des impayés actifs.
 *
 * Ne crée, ne modifie et ne supprime JAMAIS d'enregistrement :
 * la source de vérité des calculs reste PaymentSignalementService.
 * Ce service ne sert qu'à l'affichage, à partir de :
 *   - enrollments (type d'abonnement monthly/vip)
 *   - attendances (present/late/justified = obligation, jamais "absent")
 *   - payments (couverture du mois / du jour)
 */
class UnpaidDebtService
{
    /**
     * Statuts de présence qui génèrent une obligation de paiement.
     */
    private const OBLIGATION_STATUSES = ['present', 'late', 'justified'];

    /**
     * Signalements considérés comme impayés actifs.
     */
    private const OPEN_STATUSES = ['pending', 'sent'];

    /**
     * Impayés actifs, calculés dynamiquement.
     *
     * L'itération part des PRÉSENCES obligations (present/late/justified) :
     * une présence sans enrollment ou sans paiement doit quand même générer
     * une dette. Le type d'abonnement est résolu par enrollment actif,
     * puis dernier paiement, puis monthly par défaut.
     *
     * @param  int|null  $schoolYearId  année scolaire ciblée (null = toutes les années)
     * @param  string|null  $period  mois ciblé "Y-m" (null = tous les mois)
     */
    public function activeDebts(?int $schoolYearId = null, ?string $period = null): Collection
    {
        $debts = collect();

        $pairs = Attendance::whereIn('status', self::OBLIGATION_STATUSES)
            ->when($schoolYearId, fn ($query) => $query->where('school_year_id', $schoolYearId))
            ->select('student_id', 'subject_id', 'group_id')
            ->distinct()
            ->get();

        foreach ($pairs as $pair) {
            $type = $this->resolveTypeForPair($pair->student_id, $pair->subject_id, $pair->group_id);

            if (in_array($type, ['monthly', 'special_monthly'])) {
                $debts = $debts->concat($this->monthlyDebts($pair->student_id, $pair->subject_id, $schoolYearId, $period, $pair->group_id));
            } elseif (in_array($type, ['vip', 'vip_monthly', 'vip_per_session'])) {
                $debts = $debts->concat($this->vipDebts($pair->student_id, $pair->subject_id, $schoolYearId, $period, $pair->group_id));
            }
        }

        // Filet de sécurité : signalements stockés ouverts non couverts
        // par une dette calculée → ajoutés tels quels (rien ne disparaît).
        $storedIds = $debts->pluck('signalement.id')->filter();

        $debts = $debts->concat($this->legacyOpenSignalements($schoolYearId, $period, $storedIds));

        return $debts->sort(function ($a, $b) {
            return strcmp((string) $b->period, (string) $a->period)
                ?: strcmp((string) ($a->student?->last_name ?? ''), (string) ($b->student?->last_name ?? ''))
                ?: strcmp((string) ($a->student?->first_name ?? ''), (string) ($b->student?->first_name ?? ''));
        })->values();
    }

    /**
     * Historique : signalements stockés résolus (filtre Statut = Résolu).
     */
    public function resolvedHistory(?int $schoolYearId = null, ?string $period = null): Collection
    {
        return PaymentSignalement::with(['student', 'subject', 'payment'])
            ->where('status', 'resolved')
            ->get()
            ->filter(fn (PaymentSignalement $signalement) => $this->signalementMatchesFilters($signalement, $schoolYearId, $period))
            ->map(fn (PaymentSignalement $signalement) => (object) [
                'student' => $signalement->student,
                'subject' => $signalement->subject,
                'type' => $this->typeFor($signalement),
                'period' => $signalement->period,
                'amount_remaining' => (float) $signalement->amount_remaining,
                'status' => 'resolved',
                'signalement' => $signalement,
            ])
            ->values();
    }

    /**
     * Mois distincts avec présence (present/late/justified) pour un élève + matière.
     */
    private function obligationMonths(int $studentId, int $subjectId, ?int $schoolYearId, ?int $groupId = null): Collection
    {
        return Attendance::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->when($groupId, fn ($query) => $query->where('group_id', $groupId))
            ->whereIn('status', self::OBLIGATION_STATUSES)
            ->when($schoolYearId, fn ($query) => $query->where('school_year_id', $schoolYearId))
            ->get()
            ->pluck('date')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m'))
            ->unique()
            ->values();
    }

    /**
     * Obligations mensuelles : une seule dette par élève + matière + mois.
     *
     * Les mois proviennent des présences (present/late/justified) ;
     * "absent" ne génère jamais d'obligation.
     */
    private function monthlyDebts(int $studentId, int $subjectId, ?int $schoolYearId, ?string $period, ?int $groupId = null): Collection
    {
        $student = Student::find($studentId);
        $subject = Subject::find($subjectId);

        if (! $student || ! $subject) {
            return collect();
        }

        $months = $this->obligationMonths($studentId, $subjectId, $schoolYearId, $groupId);

        if ($period) {
            $months = $months->filter(fn (string $month) => $month === $period);
        }

        $debts = collect();

        foreach ($months as $month) {
            $date = Carbon::createFromFormat('Y-m', $month);

            $payments = Payment::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
                ->whereIn('payment_type', ['monthly', 'special_monthly'])
                ->get()
                ->filter(fn (Payment $payment) => $this->paymentCoversMonth($payment, $date));

            $hasPayment = $payments->isNotEmpty();
            $amountPaid = (float) $payments->sum('amount_paid');
            $amountDue = $this->getObligationAmount($studentId, $subjectId, $groupId);
            $remaining = max($amountDue - $amountPaid, 0);

            // Mois entièrement payé → aucune dette.
            if ($hasPayment && $remaining <= 0) {
                continue;
            }

            // Dette déjà résolue (signalement stocké résolu) → plus aucune dette active.
            if ($this->hasResolvedSignalement($studentId, $subjectId, $month, $date, $groupId)) {
                continue;
            }

            $stored = $this->openStoredSignalement($studentId, $subjectId, $month, $date, $groupId);

            $debts->push((object) [
                'student' => $student,
                'subject' => $subject,
                'type' => 'monthly',
                'period' => $month,
                'amount_due' => $amountDue,
                'amount_paid' => $amountPaid,
                'amount_remaining' => $remaining,
                'status' => $stored?->status ?? 'pending',
                'signalement' => $stored,
            ]);
        }

        return $debts;
    }

    /**
     * Obligations VIP : une dette par journée de présence non payée.
     * Chaque jour est indépendant ; les anciens jours non payés restent.
     */
    private function vipDebts(int $studentId, int $subjectId, ?int $schoolYearId, ?string $period, ?int $groupId = null): Collection
    {
        $student = Student::find($studentId);
        $subject = Subject::find($subjectId);

        if (! $student || ! $subject) {
            return collect();
        }

        $dates = Attendance::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->when($groupId, fn ($query) => $query->where('group_id', $groupId))
            ->whereIn('status', self::OBLIGATION_STATUSES)
            ->when($schoolYearId, fn ($query) => $query->where('school_year_id', $schoolYearId))
            ->get()
            ->pluck('date')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->unique()
            ->values();

        if ($period) {
            $dates = $dates->filter(fn (string $day) => str_starts_with($day, $period.'-'));
        }

        $debts = collect();

        foreach ($dates as $day) {
            $date = Carbon::createFromFormat('Y-m-d', $day);

            $payments = Payment::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
                ->whereIn('payment_type', ['vip', 'vip_monthly', 'vip_per_session'])
                ->get()
                ->filter(fn (Payment $payment) => $this->paymentCoversDay($payment, $date));

            $amountPaid = (float) $payments->sum('amount_paid');
            $amountDue = $this->getObligationAmount($studentId, $subjectId, $groupId);
            $remaining = max($amountDue - $amountPaid, 0);

            // Journée entièrement payée → aucune dette pour ce jour.
            if ($payments->isNotEmpty() && $remaining <= 0) {
                continue;
            }

            // Journée déjà résolue (signalement stocké résolu) → plus aucune dette active.
            if ($this->hasResolvedSignalement($studentId, $subjectId, $day, $date, $groupId)) {
                continue;
            }

            $stored = PaymentSignalement::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
                ->where('period', $day)
                ->whereIn('status', self::OPEN_STATUSES)
                ->latest('id')
                ->first();

            $debts->push((object) [
                'student' => $student,
                'subject' => $subject,
                'type' => 'vip',
                'period' => $day,
                'amount_due' => $amountDue,
                'amount_paid' => $amountPaid,
                'amount_remaining' => $remaining,
                'status' => $stored?->status ?? 'pending',
                'signalement' => $stored,
            ]);
        }

        return $debts;
    }

    /**
     * Signalement stocké ouvert pour la même dette mensuelle
     * (période "Y-m" ou nom de mois legacy).
     */
    private function openStoredSignalement(int $studentId, int $subjectId, string $month, Carbon $date, ?int $groupId = null): ?PaymentSignalement
    {
        return PaymentSignalement::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
            ->whereIn('period', [
                $month,
                $this->frenchMonth((int) $date->format('m')),
            ])
            ->whereIn('status', self::OPEN_STATUSES)
            ->latest('id')
            ->first();
    }

    /**
     * Signalement stocké résolu pour la même dette (période "Y-m"/jour
     * ou nom de mois legacy). Une dette résolue ne réapparaît pas.
     */
    private function hasResolvedSignalement(int $studentId, int $subjectId, string $period, Carbon $date, ?int $groupId = null): bool
    {
        return PaymentSignalement::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
            ->whereIn('period', [
                $period,
                $this->frenchMonth((int) $date->format('m')),
            ])
            ->where('status', 'resolved')
            ->exists();
    }

    /**
     * Signalements stockés ouverts qui ne correspondent à aucune dette calculée.
     * Ils sont conservés tels quels (archive / anciennes dettes).
     */
    private function legacyOpenSignalements(?int $schoolYearId, ?string $period, Collection $coveredIds): Collection
    {
        return PaymentSignalement::with(['student', 'subject'])
            ->whereIn('status', self::OPEN_STATUSES)
            ->get()
            ->filter(function (PaymentSignalement $signalement) use ($schoolYearId, $period, $coveredIds) {
                if ($coveredIds->contains($signalement->id)) {
                    return false;
                }

                return $this->signalementMatchesFilters($signalement, $schoolYearId, $period);
            })
            ->map(fn (PaymentSignalement $signalement) => (object) [
                'student' => $signalement->student,
                'subject' => $signalement->subject,
                'type' => $this->typeFor($signalement),
                'period' => $signalement->period,
                'amount_remaining' => (float) $signalement->amount_remaining,
                'status' => $signalement->status,
                'signalement' => $signalement,
            ]);
    }

    /**
     * Un signalement stocké correspond-il aux filtres année scolaire / période ?
     *
     * L'année scolaire est résolue via school_year_id ; pour un signalement
     * legacy non lié (NULL), elle est déduite avec SchoolYear::forPeriod
     * (période en priorité, signalement_date en source d'année).
     */
    private function signalementMatchesFilters(PaymentSignalement $signalement, ?int $schoolYearId, ?string $period): bool
    {
        $storedPeriod = (string) $signalement->period;

        if ($schoolYearId) {
            $signalementSchoolYearId = (int) $signalement->school_year_id
                ? (int) $signalement->school_year_id
                : (SchoolYear::forPeriod(
                    $storedPeriod,
                    $signalement->signalement_date?->toDateString(),
                    $signalement->signalement_date?->toDateString()
                )?->id ?? null);

            if ($signalementSchoolYearId !== $schoolYearId) {
                return false;
            }
        }

        if (! $period) {
            return true;
        }

        if ($storedPeriod === $period || str_starts_with($storedPeriod, $period.'-')) {
            return true;
        }

        // Période legacy française : nom du mois + année via signalement_date.
        if ($signalement->signalement_date) {
            $signalementDate = Carbon::parse($signalement->signalement_date);

            return $signalementDate->format('Y-m') === $period
                && $storedPeriod === $this->frenchMonth((int) $signalementDate->format('m'));
        }

        return false;
    }

    /**
     * Un paiement mensuel couvre-t-il ce mois ?
     *
     * La couverture repose UNIQUEMENT sur la période du paiement ("Y-m"
     * ou nom de mois français legacy avec la même année). Le fallback
     * payment_date n'est utilisé que si le paiement n'a pas de période.
     * Un paiement d'octobre ne couvre JAMAIS un mois d'août.
     */
    private function paymentCoversMonth(Payment $payment, Carbon $date): bool
    {
        $period = trim((string) $payment->period);
        $monthKey = $date->format('Y-m');

        if ($period === $monthKey) {
            return true;
        }

        if ($period === $this->frenchMonth((int) $date->format('m'))) {
            return $payment->payment_date
                && Carbon::parse($payment->payment_date)->format('Y') === $date->format('Y');
        }

        if ($period !== '') {
            return false;
        }

        return $payment->payment_date
            && Carbon::parse($payment->payment_date)->format('Y-m') === $monthKey;
    }

    /**
     * Un paiement VIP couvre-t-il cette journée ?
     *
     * Couverture UNIQUEMENT par la période "Y-m-d" du paiement ;
     * payment_date n'est utilisé que si le paiement n'a pas de période.
     */
    private function paymentCoversDay(Payment $payment, Carbon $date): bool
    {
        $period = trim((string) $payment->period);

        if ($period === $date->format('Y-m-d')) {
            return true;
        }

        if ($period !== '') {
            return false;
        }

        return $payment->payment_date
            && Carbon::parse($payment->payment_date)->isSameDay($date);
    }

    /**
     * Type d'abonnement d'une paire élève + matière.
     *
     * 1. Si groupId fourni → résoudre depuis le groupe spécifié
     * 2. Enrollment → Group → GroupTariff (nouveau SSOT)
     * 3. Legacy Enrollment::payment_type (fallback pour group_id=NULL)
     * 4. Dernier paiement
     * 5. monthly par défaut
     */
    private function resolveTypeForPair(int $studentId, int $subjectId, ?int $groupId = null): string
    {
        // 1. Si groupId fourni, résoudre directement depuis le groupe
        if ($groupId) {
            $group = Group::find($groupId);
            $tariff = $group?->currentTariff;

            if ($tariff) {
                return $this->computePaymentType($group, $tariff);
            }
        }

        // 2. Enrollment → Group → GroupTariff
        $query = Enrollment::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('status', 'active')
            ->with('group.currentTariff');

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        $enrollment = $query->latest('id')->first();

        if ($enrollment?->group?->currentTariff) {
            return $this->computePaymentType($enrollment->group, $enrollment->group->currentTariff);
        }

        // 3. Legacy Enrollment::payment_type
        if ($enrollment?->payment_type) {
            return $enrollment->payment_type;
        }

        // 4. Dernier paiement
        $type = Payment::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->latest('id')
            ->value('payment_type');

        return $type ?? 'monthly';
    }

    /**
     * Montant de l'obligation (tarif du groupe) pour un élève + matière.
     *
     * Résout via Enrollment → Group → GroupTariff.
     * Si groupId fourni, résout directement depuis le groupe spécifié.
     * Fallback legacy via student_group si aucun enrollment actif.
     */
    private function getObligationAmount(int $studentId, int $subjectId, ?int $groupId = null): float
    {
        // 1. Si groupId fourni, résoudre directement depuis le groupe
        if ($groupId) {
            $group = Group::find($groupId);
            $tariff = $group?->currentTariff;

            return $tariff ? (float) $tariff->student_price : 0;
        }

        // 2. Résoudre via Enrollment → Group → GroupTariff
        $query = Enrollment::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('status', 'active')
            ->with('group.currentTariff');

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        $enrollment = $query->latest('id')->first();

        if ($enrollment?->group?->currentTariff) {
            return (float) $enrollment->group->currentTariff->student_price;
        }

        // 3. Fallback legacy : résoudre via student_group pivot
        $group = Group::where('subject_id', $subjectId)
            ->where('is_active', true)
            ->whereHas('students', fn ($q) => $q->where('students.id', $studentId))
            ->first();

        if (! $group) {
            return 0;
        }

        $tariff = GroupTariff::where('group_id', $group->id)
            ->where('is_active', true)
            ->latest('effective_from')
            ->first();

        return $tariff ? (float) $tariff->student_price : 0;
    }

    /**
     * Type d'abonnement d'un signalement stocké (enrollment en priorité).
     */
    private function typeFor(PaymentSignalement $signalement): string
    {
        // 1. Si le signalement a un group_id, résoudre depuis ce groupe
        if ($signalement->group_id) {
            $group = Group::find($signalement->group_id);
            $tariff = $group?->currentTariff;

            if ($tariff) {
                return $this->computePaymentType($group, $tariff);
            }
        }

        // 2. Enrollment → Group → GroupTariff
        $query = Enrollment::where('student_id', $signalement->student_id)
            ->where('subject_id', $signalement->subject_id)
            ->where('status', 'active')
            ->with('group.currentTariff');

        if ($signalement->group_id) {
            $query->where('group_id', $signalement->group_id);
        }

        $enrollment = $query->latest('id')->first();

        if ($enrollment?->group?->currentTariff) {
            return $this->computePaymentType($enrollment->group, $enrollment->group->currentTariff);
        }

        if ($enrollment?->payment_type) {
            return $enrollment->payment_type;
        }

        return Payment::where('student_id', $signalement->student_id)
            ->where('subject_id', $signalement->subject_id)
            ->latest('id')
            ->value('payment_type') ?? 'monthly';
    }

    /**
     * Calculer le type de paiement à partir du groupe et de son tarif.
     */
    private function computePaymentType(Group $group, GroupTariff $tariff): string
    {
        if ($group->mode === 'vip') {
            return $tariff->billing_type === 'monthly' ? 'vip_monthly' : 'vip_per_session';
        }

        if ($group->mode === 'special') {
            return 'special_monthly';
        }

        return 'monthly';
    }

    private function frenchMonth(int $month): string
    {
        return match ($month) {
            1 => 'Janvier',
            2 => 'Février',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Août',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Décembre',
            default => '',
        };
    }
}
