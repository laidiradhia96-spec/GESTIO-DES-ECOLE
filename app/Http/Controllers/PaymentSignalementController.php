<?php

namespace App\Http\Controllers;

use App\Models\PaymentSignalement;
use App\Models\Subject;
use App\Services\PaymentSignalementService;
use App\Services\UnpaidDebtService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentSignalementController extends Controller
{
    /**
     * Liste des impayés actifs, calculée dynamiquement
     * (Attendance + Enrollment + Payment) — lecture seule.
     */
    public function index(Request $request)
    {
        $service = app(UnpaidDebtService::class);

        // =========================
        // ANNÉE + PÉRIODE
        // =========================

        $years = $service->availableYears();

        $year = $request->filled('year') ? (int) $request->year : (int) now()->format('Y');

        $period = $request->filled('period') ? $request->period : null;

        $status = $request->filled('status') ? $request->status : null;

        // =========================
        // MOIS DE L'ANNÉE SÉLECTIONNÉE
        // =========================

        $months = collect(range(1, 12))->map(function (int $month) use ($year) {

            return [
                'value' => $year.'-'.str_pad((string) $month, 2, '0', STR_PAD_LEFT),
                'label' => $this->monthName($month).' '.$year,
            ];
        })->all();

        // =========================
        // LIGNES D'IMPAYÉS (DYNAMIQUES)
        // =========================

        $rows = $status === 'resolved'
            ? $service->resolvedHistory($year, $period)
            : $service->activeDebts($year, $period);

        // =========================
        // RECHERCHE ÉLÈVE / PARENT
        // =========================

        if ($request->filled('search')) {

            $search = mb_strtolower(trim($request->search));

            $rows = $rows->filter(function ($row) use ($search) {

                $student = $row->student;

                return $student
                    && (mb_strpos(mb_strtolower((string) $student->first_name), $search) !== false
                        || mb_strpos(mb_strtolower((string) $student->last_name), $search) !== false
                        || mb_strpos(mb_strtolower((string) $student->parent_name), $search) !== false);
            });
        }

        // =========================
        // FILTRE MATIÈRE
        // =========================

        if ($request->filled('subject_id')) {

            $rows = $rows->filter(function ($row) use ($request) {

                return $row->subject
                    && (int) $row->subject->id === (int) $request->subject_id;
            });
        }

        // =========================
        // FILTRE STATUT
        // =========================

        if (in_array($status, ['pending', 'sent'], true)) {

            $rows = $rows->filter(fn ($row) => $row->status === $status);
        }

        // =========================
        // STATISTIQUES (enregistrements stockés)
        // =========================

        $pendingCount = PaymentSignalement::where(
            'status',
            'pending'
        )->count();

        $sentCount = PaymentSignalement::where(
            'status',
            'sent'
        )->count();

        $resolvedCount = PaymentSignalement::where(
            'status',
            'resolved'
        )->count();

        $totalRemaining = PaymentSignalement::whereIn(
            'status',
            ['pending', 'sent']
        )->sum('amount_remaining');

        // =========================
        // PAGINATION
        // =========================

        $page = LengthAwarePaginator::resolveCurrentPage();

        $perPage = 10;

        $signalements = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        // =========================
        // LIBELLÉS DE PÉRIODE (AFFICHAGE)
        // =========================

        $signalements->getCollection()->transform(function ($row) {

            $row->period_label = $this->formatPeriod(
                (string) $row->period
            );

            return $row;
        });

        // =========================
        // MATIÈRES
        // =========================

        $subjects = Subject::orderBy('name')->get();

        return view(
            'payment-signalements.index',
            compact(
                'signalements',
                'subjects',
                'months',
                'years',
                'year',
                'pendingCount',
                'sentCount',
                'resolvedCount',
                'totalRemaining'
            )
        );
    }

    /**
     * Génération automatique des signalements
     *
     * RÈGLES :
     *
     * MENSUEL :
     * - Une seule obligation par élève + matière + mois/année.
     * - Aucun paiement pour le mois  → un signalement impayé.
     * - Paiement couvrant le mois     → signalement résolu.
     * - Paiement partiel              → signalement avec le reste.
     *
     * VIP :
     * - Une obligation par élève + matière + journée exacte.
     * - Une dette d'une journée n'est jamais réglée par le paiement
     *   d'une autre journée.
     */
    public function generate()
    {
        app(PaymentSignalementService::class)->regenerateAll();

        return redirect()
            ->route('payment-signalements.index')
            ->with(
                'success',
                'Les signalements d\'impayés ont été actualisés avec succès.'
            );
    }

    /**
     * Afficher un signalement
     */
    public function show(
        PaymentSignalement $paymentSignalement
    ) {

        $paymentSignalement->load([
            'student',
            'payment',
            'subject',
        ]);

        return view(
            'payment-signalements.show',
            compact('paymentSignalement')
        );
    }

    /**
     * Marquer un signalement comme envoyé
     */
    public function markAsSent(
        PaymentSignalement $paymentSignalement
    ) {

        $paymentSignalement->update([

            'status' => 'sent',

            'sent_at' => now(),
        ]);

        return redirect()
            ->route('payment-signalements.index')
            ->with(
                'success',
                'Le signalement a été marqué comme envoyé.'
            );
    }

    /**
     * Marquer un signalement comme résolu
     */
    public function markAsResolved(
        PaymentSignalement $paymentSignalement
    ) {

        $paymentSignalement->update([

            'status' => 'resolved',

            'amount_remaining' => 0,
        ]);

        return redirect()
            ->route('payment-signalements.index')
            ->with(
                'success',
                'Le signalement a été marqué comme résolu.'
            );
    }

    /**
     * Nom du mois en français (affichage uniquement).
     */
    private function monthName(int $month): string
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
     * Libellé lisible d'une période :
     * "2026-08" → "Août 2026", "2026-08-05" → "05 Août 2026".
     */
    private function formatPeriod(string $period): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {

            [$year, $month, $day] = array_map('intval', explode('-', $period));

            return str_pad((string) $day, 2, '0', STR_PAD_LEFT)
                .' '.$this->monthName($month).' '.$year;
        }

        if (preg_match('/^\d{4}-\d{2}$/', $period)) {

            [$year, $month] = array_map('intval', explode('-', $period));

            return $this->monthName($month).' '.$year;
        }

        return $period;
    }
}
