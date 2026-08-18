<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Services\PaymentSignalementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Liste des paiements
     */
    public function index(Request $request)
    {
        $query = Payment::with([
            'student',
            'subject',
        ]);

        // =========================
        // FILTRE ANNÉE SCOLAIRE
        // =========================

        $schoolYears = SchoolYear::orderByDesc('start_date')->get();

        $schoolYearId = $request->filled('school_year_id')
            ? (int) $request->school_year_id
            : null;

        $query->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId));

        // =========================
        // RECHERCHE
        // =========================

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->whereHas('student', function ($q) use ($search) {

                $q->search($search);
            });
        }

        // =========================
        // FILTRE MATIÈRE
        // =========================

        if ($request->filled('subject_id')) {

            $query->where(
                'subject_id',
                $request->subject_id
            );
        }

        // =========================
        // FILTRE TYPE
        // =========================

        if ($request->filled('payment_type')) {

            $query->where(
                'payment_type',
                $request->payment_type
            );
        }

        // =========================
        // FILTRE PÉRIODE
        // =========================

        if ($request->filled('period')) {

            $query->where(
                'period',
                $request->period
            );
        }

        // =========================
        // FILTRE STATUT
        // =========================

        if ($request->filled('status')) {

            if ($request->status === 'paid') {

                $query->where('remaining_amount', '<=', 0);

            } elseif ($request->status === 'partial') {

                $query->where('remaining_amount', '>', 0)
                    ->where('amount_paid', '>', 0);

            } elseif ($request->status === 'unpaid') {

                $query->where('amount_paid', 0);
            }
        }

        // =========================
        // STATISTIQUES (scope année scolaire)
        // =========================

        $statsQuery = Payment::query();

        $statsQuery->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId));

        $totalPaid = (clone $statsQuery)->sum('amount_paid');

        $totalDue = (clone $statsQuery)->sum('amount_due');

        $totalRemaining = (clone $statsQuery)->sum('remaining_amount');

        $paidCount = (clone $statsQuery)
            ->where('remaining_amount', '<=', 0)
            ->where('amount_paid', '>', 0)
            ->count();

        $partialCount = (clone $statsQuery)
            ->where('remaining_amount', '>', 0)
            ->where('amount_paid', '>', 0)
            ->count();

        $unpaidCount = (clone $statsQuery)
            ->where('amount_paid', 0)
            ->count();

        // =========================
        // LISTE
        // =========================

        $payments = $query
            ->latest('payment_date')
            ->latest('payment_time')
            ->paginate(10)
            ->withQueryString();

        $subjects = Subject::orderBy('name')->get();

        return view(
            'payments.index',
            compact(
                'payments',
                'subjects',
                'schoolYears',
                'schoolYearId',
                'totalPaid',
                'totalDue',
                'totalRemaining',
                'paidCount',
                'partialCount',
                'unpaidCount'
            )
        );
    }

    /**
     * Formulaire de paiement
     */
    public function create()
    {
        $students = Student::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $subjects = Subject::query()
            ->orderBy('name')
            ->get();

        return view(
            'payments.create',
            compact(
                'students',
                'subjects'
            )
        );
    }

    /**
     * Enregistrer un paiement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'student_id' => [
                'required',
                'exists:students,id',
            ],

            'subject_id' => [
                'required',
                'exists:subjects,id',
            ],

            'payment_type' => [
                'required',
                'in:monthly,vip',
            ],

            'period' => [
                'required',
                'string',
                'max:100',
            ],

            'amount_due' => [
                'required',
                'numeric',
                'min:0',
            ],

            'amount_paid' => [
                'required',
                'numeric',
                'min:0',
            ],

            'payment_method' => [
                'required',
                'string',
                'max:50',
            ],

            'payment_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'payment_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'note' => [
                'nullable',
                'string',
            ],
        ]);

        // =========================
        // VÉRIFICATION MONTANT
        // =========================

        if (
            $validated['amount_paid']
            > $validated['amount_due']
        ) {

            return back()
                ->withErrors([
                    'amount_paid' => 'Le montant payé ne peut pas dépasser le montant demandé.',
                ])
                ->withInput();
        }

        // =========================
        // VÉRIFICATION INSCRIPTION
        // =========================

        $isEnrolled = Enrollment::query()
            ->where('student_id', $validated['student_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('status', 'active')
            ->exists();

        if (! $isEnrolled) {

            return back()
                ->withErrors([
                    'student_id' => "L'élève sélectionné n'est pas inscrit à cette matière.",
                ])
                ->withInput();
        }

        DB::transaction(function () use ($validated) {

            // =========================
            // NUMÉRO DE REÇU
            // =========================

            $lastPayment = Payment::latest('id')->first();

            $nextNumber = $lastPayment
                ? $lastPayment->id + 1
                : 1;

            $receiptNumber =
                'REC-'.
                now()->format('Y').
                '-'.
                str_pad(
                    $nextNumber,
                    5,
                    '0',
                    STR_PAD_LEFT
                );

            // =========================
            // CALCUL DU RESTE
            // =========================

            $remaining =
                (float) $validated['amount_due']
                -
                (float) $validated['amount_paid'];

            // =========================
            // CRÉATION
            // =========================

            $payment = Payment::create([

                'receipt_number' => $receiptNumber,

                'student_id' => $validated['student_id'],

                'subject_id' => $validated['subject_id'],

                'payment_type' => $validated['payment_type'],

                'period' => $validated['period'],

                'amount_due' => $validated['amount_due'],

                'amount_paid' => $validated['amount_paid'],

                'remaining_amount' => $remaining,

                'payment_method' => $validated['payment_method'],

                'payment_date' => $validated['payment_date'] ?? now()->toDateString(),

                'payment_time' => isset($validated['payment_time'])
                    ? $validated['payment_time'].':00'
                    : now()->format('H:i:s'),

                'note' => $validated['note'] ?? null,

                'school_year_id' => SchoolYear::forPeriod(
                    $validated['period'],
                    $validated['payment_date'] ?? null,
                    $validated['payment_date'] ?? null
                )?->id,
            ]);

            // =========================
            // MISE À JOUR DES SIGNALEMENTS
            // =========================

            app(PaymentSignalementService::class)
                ->syncFromPayment($payment);
        });

        return redirect()
            ->route('payments.index')
            ->with(
                'success',
                'Paiement enregistré avec succès.'
            );
    }

    /**
     * Afficher le paiement
     */
    public function show(Payment $payment)
    {
        $payment->load([
            'student',
            'subject',
            'signalements',
            'student.enrollments' => function ($query) use ($payment) {
                $query->where('subject_id', $payment->subject_id)
                    ->where('status', 'active')
                    ->latest('id')
                    ->with('teacher');
            },
        ]);

        $enrollment = $payment->student->enrollments->first();

        return view(
            'payments.show',
            compact('payment', 'enrollment')
        );
    }

    /**
     * Liste des paiements non soldés
     */
    public function unpaid(Request $request)
    {
        $query = Payment::with([
            'student',
            'subject',
        ])->where('remaining_amount', '>', 0);

        // =========================
        // RECHERCHE
        // =========================

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->whereHas('student', function ($q) use ($search) {

                $q->search($search);
            });
        }

        // =========================
        // FILTRE MATIÈRE
        // =========================

        if ($request->filled('subject_id')) {

            $query->where(
                'subject_id',
                $request->subject_id
            );
        }

        // =========================
        // FILTRE TYPE
        // =========================

        if ($request->filled('payment_type')) {

            $query->where(
                'payment_type',
                $request->payment_type
            );
        }

        // =========================
        // FILTRE PÉRIODE
        // =========================

        if ($request->filled('period')) {

            $query->where(
                'period',
                $request->period
            );
        }

        // =========================
        // LISTE
        // =========================

        $payments = $query
            ->latest('payment_date')
            ->latest('payment_time')
            ->paginate(10)
            ->withQueryString();

        $subjects = Subject::orderBy('name')->get();

        return view(
            'payments.unpaid',
            compact(
                'payments',
                'subjects'
            )
        );
    }

    /**
     * Impression du reçu
     */
    public function print(Payment $payment)
    {
        $payment->load([
            'student',
            'subject',
            'student.enrollments' => function ($query) use ($payment) {
                $query->where('subject_id', $payment->subject_id)
                    ->where('status', 'active')
                    ->latest('id')
                    ->with('teacher');
            },
        ]);

        $enrollment = $payment->student->enrollments->first();

        return view(
            'payments.print',
            compact('payment', 'enrollment')
        );
    }

    /**
     * Formulaire de modification
     */
    public function edit(Payment $payment)
    {
        $students = Student::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $subjects = Subject::query()
            ->orderBy('name')
            ->get();

        $payment->load([
            'student',
            'subject',
        ]);

        return view(
            'payments.edit',
            compact(
                'payment',
                'students',
                'subjects'
            )
        );
    }

    /**
     * Mettre à jour un paiement
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([

            'subject_id' => [
                'required',
                'exists:subjects,id',
            ],

            'payment_type' => [
                'required',
                'in:monthly,vip',
            ],

            'period' => [
                'required',
                'string',
                'max:100',
            ],

            'amount_due' => [
                'required',
                'numeric',
                'min:0',
            ],

            'amount_paid' => [
                'required',
                'numeric',
                'min:0',
            ],

            'payment_method' => [
                'required',
                'string',
                'max:50',
            ],

            'payment_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'payment_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'note' => [
                'nullable',
                'string',
            ],
        ]);

        // =========================
        // VÉRIFICATION MONTANT
        // =========================

        if (
            $validated['amount_paid']
            > $validated['amount_due']
        ) {

            return back()
                ->withErrors([
                    'amount_paid' => 'Le montant payé ne peut pas dépasser le montant demandé.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $payment) {

            // =========================
            // CALCUL DU RESTE
            // =========================

            $remaining =
                (float) $validated['amount_due']
                -
                (float) $validated['amount_paid'];

            // =========================
            // MISE À JOUR
            // =========================

            $payment->update([

                'subject_id' => $validated['subject_id'],

                'payment_type' => $validated['payment_type'],

                'period' => $validated['period'],

                'amount_due' => $validated['amount_due'],

                'amount_paid' => $validated['amount_paid'],

                'remaining_amount' => $remaining,

                'payment_method' => $validated['payment_method'],

                'payment_date' => $validated['payment_date'] ?? $payment->payment_date?->toDateString(),

                'payment_time' => isset($validated['payment_time'])
                    ? $validated['payment_time'].':00'
                    : $payment->payment_time,

                'note' => $validated['note'] ?? null,
            ]);

            // =========================
            // MISE À JOUR DES SIGNALEMENTS
            // =========================

            app(PaymentSignalementService::class)
                ->syncFromPayment($payment);
        });

        return redirect()
            ->route('payments.index')
            ->with(
                'success',
                'Paiement modifié avec succès.'
            );
    }
}
