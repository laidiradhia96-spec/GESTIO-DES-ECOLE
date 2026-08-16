<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentSignalement;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentSignalementService
{
    private const OPEN_STATUSES = ['pending', 'sent'];

    /**
     * Mettre à jour les signalements après une présence.
     */
    public function syncFromAttendance(Attendance $attendance): void
    {
        if (! $attendance->student_id || ! $attendance->subject_id || ! $attendance->date) {
            return;
        }

        $date = Carbon::parse($attendance->date);
        $type = $this->resolveSubscriptionType($attendance->student_id, $attendance->subject_id);

        if ($type === 'monthly') {
            $this->syncMonthly($attendance->student_id, $attendance->subject_id, $date);
        } elseif ($type === 'vip') {
            $this->syncVip($attendance->student_id, $attendance->subject_id, $date);
        }
    }

    /**
     * Réconcilier les signalements après la suppression d'une présence.
     *
     * Un signalement ouvert n'est supprimé que si AUCUNE autre présence
     * (présent / retard / justifié) ne justifie encore la période ET si
     * aucun paiement ne couvre la période. L'historique résolu est conservé.
     */
    public function reconcileAfterAttendanceRemoval(int $studentId, int $subjectId, string $date): void
    {
        if (! $studentId || ! $subjectId || ! $date) {
            return;
        }

        $date = Carbon::parse($date);
        $type = $this->resolveSubscriptionType($studentId, $subjectId);

        if ($type === 'monthly') {
            $this->removeMonthlyIfUnjustified($studentId, $subjectId, $date);
        } elseif ($type === 'vip') {
            $this->removeVipIfUnjustified($studentId, $subjectId, $date);
        }
    }

    /**
     * Supprimer les signalements mensuels ouverts si la période
     * n'est plus justifiée par aucune présence ni aucun paiement.
     */
    private function removeMonthlyIfUnjustified(int $studentId, int $subjectId, Carbon $date): void
    {
        $hasOtherAttendance = Attendance::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->whereIn('status', ['present', 'late', 'justified'])
            ->whereDate('date', '>=', $date->copy()->startOfMonth()->toDateString())
            ->whereDate('date', '<=', $date->copy()->endOfMonth()->toDateString())
            ->exists();

        if ($hasOtherAttendance) {
            return;
        }

        $hasCoveringPayment = Payment::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('payment_type', 'monthly')
            ->get()
            ->contains(fn (Payment $payment) => $this->paymentCoversMonth($payment, $date));

        if ($hasCoveringPayment) {
            return;
        }

        PaymentSignalement::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->whereIn('period', [
                $date->format('Y-m'),
                $this->frenchMonth((int) $date->format('m')),
            ])
            ->whereIn('status', self::OPEN_STATUSES)
            ->delete();
    }

    /**
     * Supprimer les signalements VIP ouverts si la journée
     * n'est plus justifiée par aucune présence ni aucun paiement.
     */
    private function removeVipIfUnjustified(int $studentId, int $subjectId, Carbon $date): void
    {
        $hasOtherAttendance = Attendance::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->whereIn('status', ['present', 'late', 'justified'])
            ->whereDate('date', $date->toDateString())
            ->exists();

        if ($hasOtherAttendance) {
            return;
        }

        $hasCoveringPayment = Payment::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('payment_type', 'vip')
            ->get()
            ->contains(fn (Payment $payment) => $this->paymentCoversDay($payment, $date));

        if ($hasCoveringPayment) {
            return;
        }

        PaymentSignalement::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('period', $date->format('Y-m-d'))
            ->whereIn('status', self::OPEN_STATUSES)
            ->delete();
    }

    /**
     * Mettre à jour les signalements après l'enregistrement d'un paiement.
     */
    public function syncFromPayment(Payment $payment): void
    {
        if (! $payment->student_id || ! $payment->subject_id) {
            return;
        }

        if ($payment->payment_type === 'monthly') {
            $this->syncMonthly($payment->student_id, $payment->subject_id, $this->resolvePaymentMonth($payment));
        } elseif ($payment->payment_type === 'vip') {
            $this->syncVip($payment->student_id, $payment->subject_id, $this->resolvePaymentDay($payment));
        }
    }

    /**
     * Régénérer tous les signalements depuis les présences.
     */
    public function regenerateAll(): void
    {
        DB::transaction(function () {
            Attendance::whereIn('status', ['present', 'late', 'justified'])
                ->orderBy('date')
                ->get()
                ->each(fn (Attendance $attendance) => $this->syncFromAttendance($attendance));
        });
    }

    /**
     * Type d'abonnement : enrollment actif en priorité, puis dernier paiement.
     */
    private function resolveSubscriptionType(int $studentId, int $subjectId): ?string
    {
        $type = Enrollment::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('status', 'active')
            ->latest('id')
            ->value('payment_type');

        if ($type) {
            return $type;
        }

        return Payment::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->latest('id')
            ->value('payment_type');
    }

    /**
     * Obligation MENSUELLE :
     * une seule par élève + matière + mois/année.
     *
     * Cas 1 : aucun paiement pour le mois  → signalement impayé (1 seul).
     * Cas 2 : paiement couvrant tout le mois → résoudre, aucun signalement.
     * Cas 3 : paiement partiel → signalement avec le reste à payer.
     */
    private function syncMonthly(int $studentId, int $subjectId, Carbon $date): void
    {
        $payments = Payment::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('payment_type', 'monthly')
            ->get()
            ->filter(fn (Payment $payment) => $this->paymentCoversMonth($payment, $date));

        $hasPayment = $payments->isNotEmpty();
        $amountDue = (float) $payments->sum('amount_due');
        $amountPaid = (float) $payments->sum('amount_paid');
        $remaining = max($amountDue - $amountPaid, 0);
        $lastPayment = $payments->sortByDesc('id')->first();
        $period = $date->format('Y-m');

        $open = $this->openMonthlySignalements($studentId, $subjectId, $date);

        // Cas 2 : mois entièrement payé → résoudre les signalements ouverts
        if ($hasPayment && $remaining <= 0) {
            $open->each(fn (PaymentSignalement $signalement) => $signalement->update([
                'status' => 'resolved',
                'amount_remaining' => 0,
            ]));

            return;
        }

        // Cas 1 (aucun paiement) / Cas 3 (partiel) : un seul signalement ouvert
        $signalement = $open->sortByDesc('id')->first();

        if ($signalement) {
            $signalement->update([
                'period' => $period,
                'payment_id' => $lastPayment?->id,
                'amount_remaining' => $remaining,
                'signalement_date' => now()->toDateString(),
                'note' => 'Reste à payer pour l\'abonnement mensuel.',
            ]);

            return;
        }

        PaymentSignalement::create([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'payment_id' => $lastPayment?->id,
            'period' => $period,
            'amount_remaining' => $remaining,
            'status' => 'pending',
            'signalement_date' => now()->toDateString(),
            'attendance_date' => $date->toDateString(),
            'sent_at' => null,
            'note' => 'Abonnement mensuel non payé pour '.$this->frenchMonth((int) $date->format('m')).' '.$date->format('Y').'.',
        ]);
    }

    /**
     * Obligation VIP : une par élève + matière + journée exacte.
     * Les dettes des autres journées ne sont jamais touchées.
     */
    private function syncVip(int $studentId, int $subjectId, Carbon $date): void
    {
        $payment = Payment::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('payment_type', 'vip')
            ->get()
            ->first(fn (Payment $payment) => $this->paymentCoversDay($payment, $date));

        $remaining = $payment ? (float) $payment->remaining_amount : 0;
        $period = $date->format('Y-m-d');

        // Journée entièrement payée → résoudre uniquement cette journée
        if ($payment && $remaining <= 0) {
            PaymentSignalement::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->where('period', $period)
                ->whereIn('status', self::OPEN_STATUSES)
                ->update(['status' => 'resolved', 'amount_remaining' => 0]);

            return;
        }

        $signalement = PaymentSignalement::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('period', $period)
            ->whereIn('status', self::OPEN_STATUSES)
            ->latest('id')
            ->first();

        if ($signalement) {
            $signalement->update([
                'payment_id' => $payment?->id,
                'amount_remaining' => $remaining,
            ]);

            return;
        }

        PaymentSignalement::create([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'payment_id' => $payment?->id,
            'period' => $period,
            'amount_remaining' => $remaining,
            'status' => 'pending',
            'signalement_date' => now()->toDateString(),
            'attendance_date' => $date->toDateString(),
            'sent_at' => null,
            'note' => $payment
                ? 'Reste à payer pour le paiement VIP du jour.'
                : 'Paiement VIP du jour non réglé.',
        ]);
    }

    /**
     * Un paiement mensuel couvre-t-il ce mois ?
     *
     * La couverture repose UNIQUEMENT sur la période du paiement ("Y-m"
     * ou nom de mois français legacy avec la même année). Le fallback
     * payment_date n'est utilisé que si le paiement n'a pas de période.
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
     * Signalements mensuels ouverts (Y-m ou nom de mois legacy).
     */
    private function openMonthlySignalements(int $studentId, int $subjectId, Carbon $date): Collection
    {
        return PaymentSignalement::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->whereIn('period', [
                $date->format('Y-m'),
                $this->frenchMonth((int) $date->format('m')),
            ])
            ->whereIn('status', self::OPEN_STATUSES)
            ->get();
    }

    /**
     * Mois couvert par un paiement mensuel.
     * Une période nommée legacy ("Octobre") cible le vrai mois, pas payment_date.
     */
    private function resolvePaymentMonth(Payment $payment): Carbon
    {
        $period = trim((string) $payment->period);

        if (preg_match('/^\d{4}-\d{2}$/', $period)) {
            return Carbon::createFromFormat('Y-m', $period);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {
            return Carbon::parse($period)->startOfMonth();
        }

        $monthNumber = $this->frenchMonthNumber($period);

        if ($monthNumber !== null) {
            $year = $payment->payment_date
                ? (int) Carbon::parse($payment->payment_date)->format('Y')
                : (int) now()->format('Y');

            return Carbon::create($year, $monthNumber, 1);
        }

        return Carbon::parse($payment->payment_date)->startOfMonth();
    }

    /**
     * Journée couverte par un paiement VIP.
     */
    private function resolvePaymentDay(Payment $payment): Carbon
    {
        $period = trim((string) $payment->period);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {
            return Carbon::parse($period);
        }

        return Carbon::parse($payment->payment_date);
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

    /**
     * Numéro de mois à partir d'un nom français (legacy), sinon null.
     */
    private function frenchMonthNumber(string $name): ?int
    {
        $months = [
            'janvier' => 1,
            'février' => 2,
            'fevrier' => 2,
            'mars' => 3,
            'avril' => 4,
            'mai' => 5,
            'juin' => 6,
            'juillet' => 7,
            'août' => 8,
            'aout' => 8,
            'septembre' => 9,
            'octobre' => 10,
            'novembre' => 11,
            'décembre' => 12,
            'decembre' => 12,
        ];

        return $months[mb_strtolower(trim($name))] ?? null;
    }
}
