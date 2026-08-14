<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentSignalement;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentSignalementController extends Controller
{
    /**
     * Liste des signalements d'impayés
     */
    public function index(Request $request)
    {
        $query = PaymentSignalement::with([
            'student',
            'payment',
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

            $query->where(
                'status',
                $request->status
            );
        }

        // =========================
        // STATISTIQUES
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
        // LISTE
        // =========================

        $signalements = $query
            ->latest('signalement_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        // =========================
        // MATIÈRES
        // =========================

        $subjects = Subject::orderBy('name')->get();

        return view(
            'payment-signalements.index',
            compact(
                'signalements',
                'subjects',
                'pendingCount',
                'sentCount',
                'resolvedCount',
                'totalRemaining'
            )
        );
    }


    /**
     * Créer automatiquement les signalements
     * pour les paiements avec un reste à payer.
     */
    public function generate()
{
    $payments = Payment::with([
        'student',
        'subject',
    ])
        ->where('remaining_amount', '>', 0)
        ->get();

    $created = 0;

    DB::transaction(function () use ($payments, &$created) {

        foreach ($payments as $payment) {

            // إذا كان الدفع غير مرتبط بمادة نتجاهله
            if (!$payment->subject_id) {
                continue;
            }

            // منع إنشاء نفس signalement أكثر من مرة
            $exists = PaymentSignalement::where(
                'payment_id',
                $payment->id
            )->exists();

            if (!$exists) {

                PaymentSignalement::create([

                    'student_id' =>
                        $payment->student_id,

                    'subject_id' =>
                        $payment->subject_id,

                    'payment_id' =>
                        $payment->id,

                    'period' =>
                        $payment->period,

                    'amount_remaining' =>
                        $payment->remaining_amount,

                    'status' =>
                        'pending',

                    'signalement_date' =>
                        now()->toDateString(),

                    'attendance_date' =>
                        null,

                    'sent_at' =>
                        null,

                    'note' =>
                        null,
                ]);

                $created++;
            }
        }
    });

    return redirect()
        ->route('payment-signalements.index')
        ->with(
            'success',
            $created . " signalement(s) d'impayé généré(s) avec succès."
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

            'status' =>
                'sent',

            'sent_at' =>
                now(),
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

            'status' =>
                'resolved',
        ]);

        return redirect()
            ->route('payment-signalements.index')
            ->with(
                'success',
                'Le signalement a été marqué comme résolu.'
            );
    }
}