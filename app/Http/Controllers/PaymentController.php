<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Services\FirebaseNotificationService;
use App\Services\PaymentSignalementService;
use App\Services\RevenueService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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
            'group',
        ]);

        $schoolYears = SchoolYear::orderByDesc('start_date')->get();

        $schoolYearId = $request->filled('school_year_id')
            ? (int) $request->school_year_id
            : null;

        $query->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId));

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('student', fn ($q) => $q->search($search));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

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

        $payments = $query
            ->latest('payment_date')
            ->latest('payment_time')
            ->paginate(10)
            ->withQueryString();

        $subjects = Subject::orderBy('name')->get();

        return view('payments.index', compact(
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
        ));
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

        return view('payments.create', compact('students'));
    }

    /**
     * AJAX : Matières d'un élève (via ses inscriptions actives)
     */
    public function subjectsByStudent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);

        $yearId = SchoolYear::defaultId();

        $subjects = Subject::query()
            ->whereHas('enrollments', function ($q) use ($validated, $yearId) {
                $q->where('student_id', $validated['student_id'])
                    ->where('status', 'active')
                    ->when($yearId, fn ($eq) => $eq->where('school_year_id', $yearId));
            })
            ->orderBy('name')
            ->get(['subjects.id', 'name']);

        return response()->json($subjects);
    }

    /**
     * AJAX : Groupes d'un élève pour une matière donnée (via Enrollment)
     */
    public function groupsByStudentSubject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ]);

        $yearId = SchoolYear::defaultId();

        $groups = Group::query()
            ->where('subject_id', $validated['subject_id'])
            ->where('is_active', true)
            ->where('school_year_id', $yearId)
            ->whereHas('enrollments', function ($q) use ($validated) {
                $q->where('student_id', $validated['student_id'])
                    ->where('status', 'active');
            })
            ->with(['tariffs' => function ($q) {
                $q->where('is_active', true)
                    ->latest('effective_from')
                    ->limit(1);
            }])
            ->orderBy('mode')
            ->orderBy('name')
            ->get();

        return response()->json($groups);
    }

    /**
     * AJAX : Tarif actif d'un groupe
     */
    public function tariffByGroup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'group_id' => ['required', 'integer', 'exists:groups,id'],
        ]);

        $group = Group::with(['tariffs' => function ($q) {
            $q->where('is_active', true)
                ->latest('effective_from')
                ->limit(1);
        }])->find($validated['group_id']);

        $tariff = $group->tariffs->first();

        if (! $tariff) {
            return response()->json([
                'found' => false,
                'message' => 'Aucun tarif actif pour ce groupe.',
            ]);
        }

        return response()->json([
            'found' => true,
            'group_id' => $group->id,
            'group_mode' => $group->mode,
            'group_name' => $group->name,
            'tariff_id' => $tariff->id,
            'billing_type' => $tariff->billing_type,
            'student_price' => $tariff->student_price,
        ]);
    }

    /**
     * Valider la correspondance mode/billing_type
     *
     * Abonnement mensuel → mode=normal, billing_type=monthly
     * VIP / Paiement mensuel → mode=vip, billing_type=monthly
     * VIP / Paiement par séance → mode=vip, billing_type=per_session
     */
    private function paymentTypeMatchesGroup(string $paymentType, string $groupMode, string $billingType): bool
    {
        $mapping = [
            'monthly' => ['mode' => 'normal', 'billing' => 'monthly'],
            'special_monthly' => ['mode' => 'special', 'billing' => 'monthly'],
            'vip_monthly' => ['mode' => 'vip', 'billing' => 'monthly'],
            'vip_per_session' => ['mode' => 'vip', 'billing' => 'per_session'],
        ];

        $expected = $mapping[$paymentType] ?? null;

        return $expected
            && $expected['mode'] === $groupMode
            && $expected['billing'] === $billingType;
    }

    /**
     * Enregistrer un paiement
     */
    public function store(Request $request)
    {
        $groupId = (int) $request->input('group_id');

        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'payment_type' => ['required', Rule::in(['monthly', 'special_monthly', 'vip_monthly', 'vip_per_session'])],
            'period' => ['required', 'string', 'max:100'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_date' => ['nullable', 'date', 'before_or_equal:today'],
            'payment_time' => ['nullable', 'date_format:H:i'],
            'note' => ['nullable', 'string'],
        ], [
            'group_id.required' => 'Veuillez sélectionner un groupe.',
            'group_id.exists' => 'Le groupe sélectionné est invalide.',
            'payment_type.in' => 'Type d\'abonnement invalide.',
        ]);

        // =========================
        // FIND GROUP + TARIFF
        // =========================

        $group = Group::find($groupId);
        $yearId = SchoolYear::defaultId();

        if (! $group) {
            return back()
                ->withErrors([
                    'group_id' => 'Le groupe sélectionné est invalide.',
                ])
                ->withInput();
        }

        if (! $group->is_active) {
            return back()
                ->withErrors([
                    'group_id' => 'Le groupe sélectionné est inactif.',
                ])
                ->withInput();
        }

        $paymentDate = $validated['payment_date'] ?? now()->toDateString();

        $tariff = GroupTariff::where('group_id', $groupId)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $paymentDate)
            ->where(function ($q) use ($paymentDate) {
                $q->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $paymentDate);
            })
            ->latest('effective_from')
            ->first();

        if (! $tariff) {
            return back()
                ->withErrors([
                    'group_id' => 'Aucun tarif actif pour ce groupe.',
                ])
                ->withInput();
        }

        // =========================
        // BUSINESS RULES
        // =========================

        // Student must belong to the group (via enrollment)
        $isMember = Enrollment::where('student_id', $validated['student_id'])
            ->where('group_id', $groupId)
            ->where('status', 'active')
            ->exists();

        if (! $isMember) {
            return back()
                ->withErrors([
                    'student_id' => "L'élève n'est pas membre de ce groupe.",
                ])
                ->withInput();
        }

        // Group must belong to the selected subject
        if ($group->subject_id != $validated['subject_id']) {
            return back()
                ->withErrors([
                    'group_id' => 'Ce groupe n\'appartient pas à la matière sélectionnée.',
                ])
                ->withInput();
        }

        // Group must belong to current school year
        if ($group->school_year_id != $yearId) {
            return back()
                ->withErrors([
                    'group_id' => 'Ce groupe n\'appartient pas à l\'année scolaire courante.',
                ])
                ->withInput();
        }

        // payment_type must match group mode + tariff billing_type
        if (! $this->paymentTypeMatchesGroup(
            $validated['payment_type'],
            $group->mode,
            $tariff->billing_type
        )) {
            return back()
                ->withErrors([
                    'payment_type' => 'Ce type d\'abonnement n\'est pas compatible avec ce groupe.',
                ])
                ->withInput();
        }

        // Period required for monthly
        $isMonthly = in_array($validated['payment_type'], ['monthly', 'special_monthly', 'vip_monthly']);
        if ($isMonthly && empty($validated['period'])) {
            return back()
                ->withErrors([
                    'period' => 'Le mois est obligatoire pour un paiement mensuel.',
                ])
                ->withInput();
        }

        // =========================
        // COMPUTE AMOUNT_DUE FROM TARIFF (server-side)
        // =========================

        $tariffPrice = (float) $tariff->student_price;
        $amountPaid = (float) $validated['amount_paid'];

        // Calculate what's already been paid for this student+subject+period+group
        $existingPaid = (float) Payment::where('student_id', $validated['student_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('group_id', $groupId)
            ->where('period', $validated['period'])
            ->sum('amount_paid');

        $remainingObligation = max($tariffPrice - $existingPaid, 0);

        if ($amountPaid > $remainingObligation) {
            return back()
                ->withErrors([
                    'amount_paid' => 'Le montant payé ne peut pas dépasser le reste à payer pour cette période ('.number_format($remainingObligation, 2, ',', ' ').' DA).',
                ])
                ->withInput();
        }

        $amountDue = $remainingObligation;
        $remaining = $remainingObligation - $amountPaid;

        DB::transaction(function () use ($validated, $tariff, $amountDue, $amountPaid, $remaining, $yearId, $groupId) {

            $lastPayment = Payment::latest('id')->first();
            $nextNumber = $lastPayment ? $lastPayment->id + 1 : 1;

            $receiptNumber = 'REC-'.now()->format('Y').'-'.str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            $payment = Payment::create([
                'receipt_number' => $receiptNumber,
                'student_id' => $validated['student_id'],
                'subject_id' => $validated['subject_id'],
                'group_id' => $groupId,
                'payment_type' => $validated['payment_type'],
                'period' => $validated['period'],
                'amount_due' => $amountDue,
                'amount_paid' => $amountPaid,
                'remaining_amount' => $remaining,
                'teacher_share' => (float) $tariff->student_price > 0
                    ? round($amountPaid * ((float) $tariff->teacher_share / (float) $tariff->student_price), 2)
                    : 0.0,
                'academy_share' => (float) $tariff->student_price > 0
                    ? round($amountPaid * ((float) $tariff->academy_share / (float) $tariff->student_price), 2)
                    : 0.0,
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'] ?? now()->toDateString(),
                'payment_time' => isset($validated['payment_time'])
                    ? $validated['payment_time'].':00'
                    : now()->format('H:i:s'),
                'note' => $validated['note'] ?? null,
                'school_year_id' => $yearId,
            ]);

            app(PaymentSignalementService::class)
                ->syncFromPayment($payment);

            $createdPayment = $payment;
        });

        // =====================================================
        // NOTIFICATION PUSH PAIEMENT
        // =====================================================

        try {
            if (isset($createdPayment)) {
                $notificationService = app(FirebaseNotificationService::class);
                $student = Student::find($validated['student_id']);

                if ($student && $student->user) {
                    $studentName = $student->first_name.' '.$student->last_name;

                    if ($createdPayment->remaining_amount <= 0 && $createdPayment->amount_paid > 0) {
                        $notificationService->notifyPaymentCompleted(
                            $student->user,
                            $studentName,
                            $student->id,
                            $createdPayment->id
                        );
                    } elseif ($createdPayment->amount_paid > 0 && $createdPayment->remaining_amount > 0) {
                        $notificationService->notifyPayment(
                            $student->user,
                            $studentName,
                            (float) $createdPayment->amount_paid,
                            (float) $createdPayment->remaining_amount,
                            $student->id,
                            $createdPayment->id
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Payment notification failed: '.$e->getMessage());
        }

        return redirect()
            ->route('payments.index')
            ->with('success', 'Paiement enregistré avec succès.');
    }

    /**
     * Afficher le paiement
     */
    public function show(Payment $payment)
    {
        $payment->load([
            'student',
            'subject',
            'group.teacher',
            'group.currentTariff',
            'signalements',
            'student.enrollments' => function ($query) use ($payment) {
                $query->where('subject_id', $payment->subject_id)
                    ->where('group_id', $payment->group_id)
                    ->where('status', 'active')
                    ->latest('id')
                    ->with('teacher');
            },
        ]);

        $enrollment = $payment->student->enrollments->first();

        return view('payments.show', compact('payment', 'enrollment'));
    }

    /**
     * Liste des paiements non soldés
     */
    public function unpaid(Request $request)
    {
        $query = Payment::with([
            'student',
            'subject',
            'group',
        ])->where('remaining_amount', '>', 0);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('student', fn ($q) => $q->search($search));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $payments = $query
            ->latest('payment_date')
            ->latest('payment_time')
            ->paginate(10)
            ->withQueryString();

        $subjects = Subject::orderBy('name')->get();

        return view('payments.unpaid', compact('payments', 'subjects'));
    }

    /**
     * Impression du reçu
     */
    public function print(Payment $payment)
    {
        $payment->load([
            'student',
            'subject',
            'group.teacher',
            'group.currentTariff',
            'student.enrollments' => function ($query) use ($payment) {
                $query->where('subject_id', $payment->subject_id)
                    ->where('group_id', $payment->group_id)
                    ->where('status', 'active')
                    ->latest('id')
                    ->with('teacher');
            },
        ]);

        $enrollment = $payment->student->enrollments->first();

        return view('payments.print', compact('payment', 'enrollment'));
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

        $payment->load(['student', 'subject', 'group']);

        return view('payments.edit', compact('payment', 'students', 'subjects'));
    }

    /**
     * Mettre à jour un paiement
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'payment_type' => ['required', Rule::in(['monthly', 'special_monthly', 'vip_monthly', 'vip_per_session'])],
            'period' => ['required', 'string', 'max:100'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_date' => ['nullable', 'date', 'before_or_equal:today'],
            'payment_time' => ['nullable', 'date_format:H:i'],
            'note' => ['nullable', 'string'],
        ]);

        $groupId = (int) $validated['group_id'];
        $group = Group::find($groupId);

        // Resolve tariff at the payment's date (historical accuracy)
        $paymentDate = ($validated['payment_date'] ?? $payment->payment_date)
            ? Carbon::parse($validated['payment_date'] ?? $payment->payment_date)
            : Carbon::now();

        $tariff = app(RevenueService::class)->resolveTariff($groupId, $paymentDate);

        if (! $tariff) {
            return back()
                ->withErrors(['group_id' => 'Aucun tarif actif pour ce groupe à la date du paiement.'])
                ->withInput();
        }

        // Business rules
        if ($group->subject_id != $validated['subject_id']) {
            return back()
                ->withErrors(['group_id' => 'Ce groupe n\'appartient pas à la matière sélectionnée.'])
                ->withInput();
        }

        if (! $this->paymentTypeMatchesGroup(
            $validated['payment_type'],
            $group->mode,
            $tariff->billing_type
        )) {
            return back()
                ->withErrors(['payment_type' => 'Ce type d\'abonnement n\'est pas compatible avec ce groupe.'])
                ->withInput();
        }

        // Compute amount_due from tariff
        $tariffPrice = (float) $tariff->student_price;
        $amountPaid = (float) $validated['amount_paid'];

        // Calculate what's already been paid for this student+subject+period+group (excluding this payment)
        $existingPaid = (float) Payment::where('student_id', $payment->student_id)
            ->where('subject_id', $validated['subject_id'])
            ->where('group_id', $groupId)
            ->where('period', $validated['period'])
            ->where('id', '!=', $payment->id)
            ->sum('amount_paid');

        $remainingObligation = max($tariffPrice - $existingPaid, 0);

        if ($amountPaid > $remainingObligation) {
            return back()
                ->withErrors([
                    'amount_paid' => 'Le montant payé ne peut pas dépasser le reste à payer pour cette période ('.number_format($remainingObligation, 2, ',', ' ').' DA).',
                ])
                ->withInput();
        }

        $amountDue = $remainingObligation;
        $remaining = $remainingObligation - $amountPaid;

        DB::transaction(function () use ($validated, $payment, $tariff, $groupId, $amountDue, $amountPaid, $remaining) {

            $payment->update([
                'subject_id' => $validated['subject_id'],
                'group_id' => $groupId,
                'payment_type' => $validated['payment_type'],
                'period' => $validated['period'],
                'amount_due' => $amountDue,
                'amount_paid' => $amountPaid,
                'remaining_amount' => $remaining,
                'teacher_share' => (float) $tariff->student_price > 0
                    ? round($amountPaid * ((float) $tariff->teacher_share / (float) $tariff->student_price), 2)
                    : 0.0,
                'academy_share' => (float) $tariff->student_price > 0
                    ? round($amountPaid * ((float) $tariff->academy_share / (float) $tariff->student_price), 2)
                    : 0.0,
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'] ?? $payment->payment_date?->toDateString(),
                'payment_time' => isset($validated['payment_time'])
                    ? $validated['payment_time'].':00'
                    : $payment->payment_time,
                'note' => $validated['note'] ?? null,
            ]);

            app(PaymentSignalementService::class)
                ->syncFromPayment($payment);
        });

        return redirect()
            ->route('payments.index')
            ->with('success', 'Paiement modifié avec succès.');
    }
}
