<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Liste des matières
     */
    public function index()
    {
        $subjects = Subject::latest()->get();

        return view('subjects.index', compact('subjects'));
    }

    /**
     * Formulaire d'ajout
     */
    public function create()
    {
        return view('subjects.create');
    }

    /**
     * Enregistrer une nouvelle matière
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:subjects,code',
            'description' => 'nullable|string',
            'level' => 'required|string|max:100',
            'hours_per_week' => 'required|integer|min:1|max:40',
            'active' => 'nullable|boolean',
        ]);

        $validated['active'] = $request->has('active');

        Subject::create($validated);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Matière ajoutée avec succès.');
    }

    /**
     * Afficher une matière
     */
    public function show(Subject $subject)
    {
        return view('subjects.show', compact('subject'));
    }

    /**
     * Formulaire de modification
     */
    public function edit(Subject $subject)
    {
        return view('subjects.edit', compact('subject'));
    }

    /**
     * Mettre à jour une matière
     */
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:subjects,code,' . $subject->id,
            'description' => 'nullable|string',
            'level' => 'required|string|max:100',
            'hours_per_week' => 'required|integer|min:1|max:40',
            'active' => 'nullable|boolean',
        ]);

        $validated['active'] = $request->has('active');

        $subject->update($validated);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Matière modifiée avec succès.');
    }

    public function teachers(Subject $subject)
{
    $teachers = $subject->teachers()
        ->where('teachers.active', true)
        ->orderBy('first_name')
        ->orderBy('last_name')
        ->get([
            'teachers.id',
            'teachers.first_name',
            'teachers.last_name',
            'teachers.speciality',
        ]);

    return response()->json($teachers);
} 
    /**
     * Supprimer une matière
     */
    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Matière supprimée avec succès.');
    }

    public function editTeachers(Subject $subject)
{
    $teachers = \App\Models\Teacher::where('active', true)
        ->orderBy('first_name')
        ->orderBy('last_name')
        ->get();

    $subject->load('teachers');

    return view('subjects.teachers', compact('subject', 'teachers'));
}


public function updateTeachers(Request $request, Subject $subject)
{
    $validated = $request->validate([
        'teachers' => 'nullable|array',
        'teachers.*' => 'exists:teachers,id',
    ]);

    $subject->teachers()->sync($validated['teachers'] ?? []);

    return redirect()
        ->route('subjects.index')
        ->with('success', 'Les enseignants de la matière ont été mis à jour avec succès.');
}
}