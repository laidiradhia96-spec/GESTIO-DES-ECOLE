<?php

namespace App\Http\Controllers;

use App\Models\UnpaidSignalement;
use Illuminate\Http\Request;

class UnpaidSignalementController extends Controller
{
    /**
     * Liste des impayés
     */
    public function index(Request $request)
    {
        $query = UnpaidSignalement::with([
            'student',
            'payment'
        ]);

        // Recherche élève / parent
        if ($request->filled('search')) {

            $search = $request->search;

            $query->whereHas('student', function ($q) use ($search) {

                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('parent_name', 'like', "%{$search}%");
            });
        }

        // Filtre période
        if ($request->filled('period')) {

            $query->where('period', $request->period);
        }

        // Filtre statut
        if ($request->filled('status')) {

            $query->where('status', $request->status);
        }

        // Statistiques
        $totalUnpaid = UnpaidSignalement::whereIn(
            'status',
            ['pending', 'contacted']
        )->sum('amount_remaining');

        $pendingCount = UnpaidSignalement::where(
            'status',
            'pending'
        )->count();

        $contactedCount = UnpaidSignalement::where(
            'status',
            'contacted'
        )->count();

        $paidCount = UnpaidSignalement::where(
            'status',
            'paid'
        )->count();

        $signalements = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'signalements.index',
            compact(
                'signalements',
                'totalUnpaid',
                'pendingCount',
                'contactedCount',
                'paidCount'
            )
        );
    }

    /**
     * Afficher un signalement
     */
    public function show(UnpaidSignalement $signalement)
    {
        $signalement->load([
            'student',
            'payment'
        ]);

        return view(
            'signalements.show',
            compact('signalement')
        );
    }

    /**
     * Marquer comme contacté
     */
    public function contacted(UnpaidSignalement $signalement)
    {
        $signalement->update([
            'status' => 'contacted',
            'reminder_count' => $signalement->reminder_count + 1,
            'last_reminder_at' => now(),
        ]);

        return back()->with(
            'success',
            'Le signalement a été marqué comme contacté.'
        );
    }

    /**
     * Annuler le signalement
     */
    public function cancel(UnpaidSignalement $signalement)
    {
        $signalement->update([
            'status' => 'cancelled',
        ]);

        return back()->with(
            'success',
            'Le signalement a été annulé.'
        );
    }
}