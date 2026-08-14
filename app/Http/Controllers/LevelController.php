<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    /**
     * Liste des niveaux
     */
    public function index()
    {
        $levels = Level::orderBy('name')->get();

        return view('levels.index', compact('levels'));
    }

    /**
     * Formulaire d'ajout
     */
    public function create()
    {
        return view('levels.create');
    }

    /**
     * Enregistrer un niveau
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:levels,code',
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);

        $validated['active'] = $request->boolean('active');

        Level::create($validated);

        return redirect()
            ->route('levels.index')
            ->with('success', 'Le niveau a été ajouté avec succès.');
    }

    /**
     * Afficher un niveau
     */
    public function show(Level $level)
    {
        return view('levels.show', compact('level'));
    }

    /**
     * Formulaire de modification
     */
    public function edit(Level $level)
    {
        return view('levels.edit', compact('level'));
    }

    /**
     * Mettre à jour un niveau
     */
    public function update(Request $request, Level $level)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:levels,code,' . $level->id,
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);

        $validated['active'] = $request->boolean('active');

        $level->update($validated);

        return redirect()
            ->route('levels.index')
            ->with('success', 'Le niveau a été modifié avec succès.');
    }

    /**
     * Supprimer un niveau
     */
    public function destroy(Level $level)
    {
        $level->delete();

        return redirect()
            ->route('levels.index')
            ->with('success', 'Le niveau a été supprimé avec succès.');
    }
}