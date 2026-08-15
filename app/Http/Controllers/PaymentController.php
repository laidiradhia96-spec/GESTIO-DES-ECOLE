<?php

namespace App\Http\Controllers;

use App\Models\Payment;
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
        // RECHERCHE
        // =========================

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->whereHas('student', function ($q) use ($search) {

                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('parent_name', 'like', "%{$search}%");
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
        // STATISTIQUES
        // =========================

        $totalPaid = Payment::sum('amount_paid');

        $totalDue = Payment::sum('amount_due');

        $totalRemaining = Payment::sum('remaining_amount');

        $paidCount = Payment::where(
            'remaining_amount',
            '<=',
            0
        )
            ->where('amount_paid', '>', 0)
            ->count();

        $partialCount = Payment::where(
            'remaining_amount',
            '>',
            0
        )
            ->where('amount_paid', '>', 0)
            ->count();

        $unpaidCount = Payment::where(
            'amount_paid',
            0
        )->count();

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

                'payment_date' => now()->toDateString(),

                'payment_time' => now()->format('H:i:s'),

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
        ]);

        return view(
            'payments.show',
            compact('payment')
        );
    }
}
