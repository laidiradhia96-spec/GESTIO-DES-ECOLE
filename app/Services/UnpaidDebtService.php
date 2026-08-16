<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentSignalement;
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
     * @param  int|null  $year  année ciblée (null = toutes les années)
     * @param  string|null  $period  mois ciblé "Y-m" (null = tous les mois)
     */
    public function activeDebts(?int $year = null, ?string $period = null): Collection
    {
        $debts = collect();

        $pairs = Attendance::whereIn('status', self::OBLIGATION_STATUSES)
            ->when($year, fn ($query) => $query->whereYear('date', $year))
            ->select('student_id', 'subject_id')
            ->distinct()
            ->get();

        foreach ($pairs as $pair) {
            $type = $this->resolveTypeForPair($pair->student_id, $pair->subject_id);

            if ($type === 'monthly') {
                $debts = $debts->concat($this->monthlyDebts($pair->student_id, $pair->subject_id, $year, $period));
            } elseif ($type === 'vip') {
                $debts = $debts->concat($this->vipDebts($pair->student_id, $pair->subject_id, $year, $period));
            }
        }

        // Filet de sécurité : signalements stockés ouverts non couverts
        // par une dette calculée → ajoutés tels quels (rien ne disparaît).
        $storedIds = $debts->pluck('signalement.id')->filter();

        $debts = $debts->concat($this->legacyOpenSignalements($year, $period, $storedIds));

        return $debts->sort(function ($a, $b) {
            return strcmp((string) $b->period, (string) $a->period)
                ?: strcmp((string) ($a->student?->last_name ?? ''), (string) ($b->student?->last_name ?? ''))
                ?: strcmp((string) ($a->student?->first_name ?? ''), (string) ($b->student?->first_name ?? ''));
        })->values();
    }

    /**
     * Historique : signalements stockés résolus (filtre Statut = Résolu).
     */
    public function resolvedHistory(?int $year = null, ?string $period = null): Collection
    {
        return PaymentSignalement::with(['student', 'subject', 'payment'])
            ->where('status', 'resolved')
            ->get()
            ->filter(fn (PaymentSignalement $signalement) => $this->signalementMatchesFilters($signalement, $year, $period))
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
     * Années disponibles pour le filtre : années présentes dans les
     * présences, les paiements et les signalements, + l'année courante.
     */
    public function availableYears(): Collection
    {
        $dates = collect();

        Attendance::whereNotNull('date')->pluck('date')->each(fn ($date) => $dates->push($date));

        Payment::whereNotNull('payment_date')->pluck('payment_date')->each(fn ($date) => $dates->push($date));

        Payment::whereNotNull('period')->pluck('period')->each(function ($period) use ($dates) {
            if (preg_match('/^\d{4}-\d{2}(-\d{2})?$/', (string) $period)) {
                $dates->push(Carbon::parse((string) $period)->toDateString());
            }
        });

        PaymentSignalement::whereNotNull('signalement_date')->pluck('signalement_date')->each(fn ($date) => $dates->push($date));

        return collect([(int) now()->format('Y')])
            ->merge($dates->filter()->map(fn ($date) => (int) Carbon::parse($date)->format('Y')))
            ->unique()
            ->sortDesc()
            ->values();
    }

    /**
     * Obligations mensuelles : une seule dette par élève + matière + mois.
     *
     * Les mois proviennent des présences (present/late/justified) ;
     * "absent" ne génère jamais d'obligation.
     */
    private function monthlyDebts(int $studentId, int $subjectId, ?int $year, ?string $period): Collection
    {
        $student = Student::find($studentId);
        $subject = Subject::find($subjectId);

        if (! $student || ! $subject) {
            return collect();
        }

        $months = $this->obligationMonths($studentId, $subjectId, $year);

        if ($period) {
            $months = $months->filter(fn (string $month) => $month === $period);
        }

        $debts = collect();

        foreach ($months as $month) {
            $date = Carbon::createFromFormat('Y-m', $month);

            $payments = Payment::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->where('payment_type', 'monthly')
                ->get()
                ->filter(fn (Payment $payment) => $this->paymentCoversMonth($payment, $date));

            $hasPayment = $payments->isNotEmpty();
            $amountDue = (float) $payments->sum('amount_due');
            $amountPaid = (float) $payments->sum('amount_paid');
            $remaining = max($amountDue - $amountPaid, 0);

            // Mois entièrement payé → aucune dette.
            if ($hasPayment && $remaining <= 0) {
                continue;
            }

            // Dette déjà résolue (signalement stocké résolu) → plus aucune dette active.
            if ($this->hasResolvedSignalement($studentId, $subjectId, $month, $date)) {
                continue;
            }

            $stored = $this->openStoredSignalement($studentId, $subjectId, $month, $date);

            $debts->push((object) [
                'student' => $student,
                'subject' => $subject,
                'type' => 'monthly',
                'period' => $month,
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
    private function vipDebts(int $studentId, int $subjectId, ?int $year, ?string $period): Collection
    {
        $student = Student::find($studentId);
        $subject = Subject::find($subjectId);

        if (! $student || ! $subject) {
            return collect();
        }

        $dates = Attendance::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->whereIn('status', self::OBLIGATION_STATUSES)
            ->when($year, fn ($query) => $query->whereYear('date', $year))
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

            $payment = Payment::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->where('payment_type', 'vip')
                ->get()
                ->first(fn (Payment $payment) => $this->paymentCoversDay($payment, $date));

            // Journée entièrement payée → aucune dette pour ce jour.
            if ($payment && (float) $payment->remaining_amount <= 0) {
                continue;
            }

            // Journée déjà résolue (signalement stocké résolu) → plus aucune dette active.
            if ($this->hasResolvedSignalement($studentId, $subjectId, $day, $date)) {
                continue;
            }

            $stored = PaymentSignalement::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->where('period', $day)
                ->whereIn('status', self::OPEN_STATUSES)
                ->latest('id')
                ->first();

            $debts->push((object) [
                'student' => $student,
                'subject' => $subject,
                'type' => 'vip',
                'period' => $day,
                'amount_remaining' => $payment ? (float) $payment->remaining_amount : 0,
                'status' => $stored?->status ?? 'pending',
                'signalement' => $stored,
            ]);
        }

        return $debts;
    }

    /**
     * Mois distincts avec présence (present/late/justified) pour un élève + matière.
     */
    private function obligationMonths(int $studentId, int $subjectId, ?int $year): Collection
    {
        return Attendance::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->whereIn('status', self::OBLIGATION_STATUSES)
            ->when($year, fn ($query) => $query->whereYear('date', $year))
            ->get()
            ->pluck('date')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m'))
            ->unique()
            ->values();
    }

    /**
     * Signalement stocké ouvert pour la même dette mensuelle
     * (période "Y-m" ou nom de mois legacy).
     */
    private function openStoredSignalement(int $studentId, int $subjectId, string $month, Carbon $date): ?PaymentSignalement
    {
        return PaymentSignalement::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
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
    private function hasResolvedSignalement(int $studentId, int $subjectId, string $period, Carbon $date): bool
    {
        return PaymentSignalement::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
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
    private function legacyOpenSignalements(?int $year, ?string $period, Collection $coveredIds): Collection
    {
        return PaymentSignalement::with(['student', 'subject'])
            ->whereIn('status', self::OPEN_STATUSES)
            ->get()
            ->filter(function (PaymentSignalement $signalement) use ($year, $period, $coveredIds) {
                if ($coveredIds->contains($signalement->id)) {
                    return false;
                }

                return $this->signalementMatchesFilters($signalement, $year, $period);
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
     * Un signalement stocké correspond-il aux filtres année / période ?
     */
    private function signalementMatchesFilters(PaymentSignalement $signalement, ?int $year, ?string $period): bool
    {
        $storedPeriod = (string) $signalement->period;

        if ($year) {
            if (preg_match('/^\d{4}-\d{2}(-\d{2})?$/', $storedPeriod)) {
                if (substr($storedPeriod, 0, 4) !== (string) $year) {
                    return false;
                }
            } elseif ($signalement->signalement_date
                && (int) Carbon::parse($signalement->signalement_date)->format('Y') !== $year) {
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
     * Type d'abonnement d'une paire élève + matière :
     * enrollment actif → dernier paiement → monthly par défaut.
     *
     * Une présence enregistrée doit toujours générer une obligation :
     * le défaut monthly évite qu'une dette disparaisse de la page Impayés.
     */
    private function resolveTypeForPair(int $studentId, int $subjectId): string
    {
        $type = Enrollment::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('status', 'active')
            ->latest('id')
            ->value('payment_type');

        if ($type) {
            return $type;
        }

        $type = Payment::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->latest('id')
            ->value('payment_type');

        return $type ?? 'monthly';
    }

    /**
     * Type d'abonnement d'un signalement stocké (enrollment en priorité).
     */
    private function typeFor(PaymentSignalement $signalement): string
    {
        $type = Enrollment::where('student_id', $signalement->student_id)
            ->where('subject_id', $signalement->subject_id)
            ->where('status', 'active')
            ->latest('id')
            ->value('payment_type');

        if ($type) {
            return $type;
        }

        return Payment::where('student_id', $signalement->student_id)
            ->where('subject_id', $signalement->subject_id)
            ->latest('id')
            ->value('payment_type') ?? 'monthly';
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
