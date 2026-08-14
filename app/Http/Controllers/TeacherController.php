<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Level;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * Liste des enseignants
     */
    public function index(Request $request)
    {
        $query = Teacher::with('levels');

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('speciality', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filtre actif / inactif
        if ($request->filled('active')) {
            $query->where('active', $request->active);
        }

        $teachers = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('teachers.index', compact('teachers'));
    }

    /**
     * Formulaire d'ajout
     */
    public function create()
    {
        $levels = Level::where('active', true)
            ->orderBy('name')
            ->get();

        return view('teachers.create', compact('levels'));
    }



    /**
     * Enregistrer un enseignant
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'speciality' => 'required|string|max:150',
        'phone' => 'nullable|string|max:30',
        'email' => 'nullable|email|max:150',
        'address' => 'nullable|string',
        'hire_date' => 'nullable|date',
        'active' => 'nullable|boolean',

        'levels' => 'nullable|array',
        'levels.*' => 'exists:levels,id',
    ]);

    $validated['active'] = $request->has('active');

    $teacher = Teacher::create($validated);

    // ربط الأستاذ بالمستويات
    $teacher->levels()->sync($request->input('levels', []));

    return redirect()
        ->route('teachers.index')
        ->with('success', 'Enseignant ajouté avec succès.');
}

    /**
     * Afficher un enseignant
     */
    public function show(Teacher $teacher)
    {
        $teacher->load('levels', 'subjects');

        return view('teachers.show', compact('teacher'));
    }

    /**
     * Formulaire de modification
     */
    public function edit(Teacher $teacher)
    {
        $levels = Level::where('active', true)
            ->orderBy('name')
            ->get();

        $teacher->load('levels');

        return view('teachers.edit', compact('teacher', 'levels'));
    }

    /**
     * Mettre à jour un enseignant
     */
    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'speciality' => 'required|string|max:150',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'hire_date' => 'nullable|date',
            'active' => 'nullable|boolean',

            'levels' => 'nullable|array',
            'levels.*' => 'exists:levels,id',
        ]);

        $validated['active'] = $request->has('active');

        $teacher->update($validated);

        // تحديث المستويات
        $teacher->levels()->sync($request->input('levels', []));

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Enseignant modifié avec succès.');
    }

    /**
     * Supprimer un enseignant
     */
    public function destroy(Teacher $teacher)
    {
        $teacher->levels()->detach();

        $teacher->delete();

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Enseignant supprimé avec succès.');
    }
}