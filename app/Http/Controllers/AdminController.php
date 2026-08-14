<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Liste des administrateurs
     */
    public function index()
    {
        $admins = User::where('role', 'admin')
            ->latest()
            ->paginate(10);

        return view('admins.index', compact('admins'));
    }

    /**
     * Formulaire d'ajout
     */
    public function create()
    {
        return view('admins.create');
    }

    /**
     * Créer un administrateur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
        ]);

        return redirect()
            ->route('admins.index')
            ->with('success', 'Administrateur créé avec succès.');
    }

    /**
     * Supprimer un administrateur
     */
    public function destroy(User $user)
    {
        // حماية الحساب الحالي
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // التأكد أنه Admin
        if ($user->role !== 'admin') {
            abort(404);
        }

        $user->delete();

        return redirect()
            ->route('admins.index')
            ->with('success', 'Administrateur supprimé avec succès.');
    }
}