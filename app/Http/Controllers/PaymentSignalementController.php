<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\PaymentSignalement;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Services\PaymentSignalementService;
use App\Services\UnpaidDebtService;
use Carbon\Carbon;
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
        // ANNÉE SCOLAIRE + PÉRIODE
        // =========================

        $schoolYears = SchoolYear::orderByDesc('start_date')->get();

        $schoolYearId = $request->filled('school_year_id')
            ? (int) $request->school_year_id
            : ($request->has('school_year_id')
                ? null
                : SchoolYear::defaultId());

        $period = $request->filled('period') ? $request->period : null;

        $status = $request->filled('status') ? $request->status : null;

        // =========================
        // MOIS DE L'ANNÉE SCOLAIRE SÉLECTIONNÉE
        // =========================

        $months = collect();

        $selectedSchoolYear = $schoolYears->firstWhere('id', $schoolYearId);

        if ($selectedSchoolYear) {

            $cursor = Carbon::parse($selectedSchoolYear->start_date)->startOfMonth();

            $end = Carbon::parse($selectedSchoolYear->end_date)->startOfMonth();

            while ($cursor->lte($end)) {

                $months->push([
                    'value' => $cursor->format('Y-m'),
                    'label' => $this->monthName((int) $cursor->format('m')).' '.$cursor->format('Y'),
                ]);

                $cursor->addMonth();
            }
        }

        $months = $months->all();

        // =========================
        // LIGNES D'IMPAYÉS (DYNAMIQUES)
        // =========================

        $activeDebts = $service->activeDebts($schoolYearId, $period);
        $resolvedDebts = $service->resolvedHistory($schoolYearId, $period);

        $rows = $status === 'resolved'
            ? $resolvedDebts
            : $activeDebts;

        // =========================
        // RECHERCHE ÉLÈVE / PARENT
        // =========================

        if ($request->filled('search')) {

            $search = mb_strtolower(trim($request->search));

            $terms = preg_split(
                '/\s+/',
                $search,
                -1,
                PREG_SPLIT_NO_EMPTY
            ) ?: [];

            if ($terms !== []) {

                $rows = $rows->filter(function ($row) use ($terms) {

                    $student = $row->student;

                    if (! $student) {
                        return false;
                    }

                    foreach ($terms as $term) {

                        $matches = mb_strpos(mb_strtolower((string) $student->first_name), $term) !== false
                            || mb_strpos(mb_strtolower((string) $student->last_name), $term) !== false
                            || mb_strpos(mb_strtolower((string) $student->phone), $term) !== false
                            || mb_strpos(mb_strtolower((string) $student->parent_name), $term) !== false;

                        if (! $matches) {
                            return false;
                        }
                    }

                    return true;
                });
            }
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
        // STATISTIQUES (scope année/période uniquement,
        // indépendantes des filtres recherche/matière/statut des lignes)
        // =========================

        $pendingCount = $activeDebts->where('status', 'pending')->count();

        $sentCount = $activeDebts->where('status', 'sent')->count();

        $resolvedCount = $resolvedDebts->count();

        $totalRemaining = $activeDebts->whereIn(
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
                'schoolYears',
                'schoolYearId',
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
     * Marquer un signalement comme envoyé.
     *
     * Fonctionne aussi pour une dette calculée dynamiquement sans
     * signalement stocké : l'obligation (student_id + subject_id + period)
     * est matérialisée avant la transition, sans créer de doublon.
     */
    public function markAsSent(Request $request)
    {
        $data = $this->validateDebtIdentity($request);

        $signalement = $this->resolveSignalementForDebt($data);

        $signalement->update([
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
     * Marquer un signalement comme résolu.
     *
     * Même comportement que markAsSent : matérialise la dette calculée
     * si nécessaire, puis la passe en résolu.
     */
    public function markAsResolved(Request $request)
    {
        $data = $this->validateDebtIdentity($request);

        $signalement = $this->resolveSignalementForDebt($data);

        $signalement->update([
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
     * Validation de l'identité d'une dette (élève + matière + groupe + période).
     */
    private function validateDebtIdentity(Request $request): array
    {
        return $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'group_id' => ['nullable', 'integer', 'exists:groups,id'],
            'period' => ['required', 'string', 'max:20'],
            'amount_remaining' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    /**
     * Signalement stocké correspondant à une dette, sinon création.
     *
     * Recherche par (student_id + subject_id + [group_id] + period),
     * avec l'alias legacy nom-de-mois français, pour ne jamais créer
     * de doublon.
     *
     * Le group_id est conservé lorsqu'il est fourni : un même élève +
     * matière peut avoir des dettes dans des groupes différents
     * (ex. Normal vs VIP).
     */
    private function resolveSignalementForDebt(array $data): PaymentSignalement
    {
        $periods = [$data['period']];

        if (preg_match('/^\d{4}-\d{2}$/', $data['period'])) {
            $periods[] = $this->monthName((int) substr($data['period'], 5, 2));
        }

        $groupId = $data['group_id'] ?? null;

        $signalement = PaymentSignalement::where('student_id', $data['student_id'])
            ->where('subject_id', $data['subject_id'])
            ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
            ->whereIn('period', $periods)
            ->whereIn('status', ['pending', 'sent', 'resolved'])
            ->latest('id')
            ->first();

        if ($signalement) {
            return $signalement;
        }

        $signalementDate = now()->toDateString();

        $schoolYearId = $groupId
            ? Group::find($groupId)?->school_year_id
            : SchoolYear::forPeriod($data['period'], $signalementDate, $signalementDate)?->id;

        return PaymentSignalement::create([
            'student_id' => $data['student_id'],
            'subject_id' => $data['subject_id'],
            'group_id' => $groupId,
            'period' => $data['period'],
            'amount_remaining' => $data['amount_remaining'] ?? 0,
            'status' => 'pending',
            'signalement_date' => $signalementDate,
            'note' => 'Dette calculée traitée manuellement.',
            'school_year_id' => $schoolYearId,
        ]);
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
