<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Payment;
use App\Models\PaymentSignalement;
use App\Models\Subject;
use App\Models\Teacher;
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

            'primaire' => 'nullable|boolean',

            'moyen' => 'nullable|boolean',

            'lycee' => 'nullable|boolean',

            'hours_per_week' => 'required|integer|min:1|max:40',

            'active' => 'nullable|boolean',
        ]);

        // Les cases à cocher
        $validated['primaire'] = $request->has('primaire');

        $validated['moyen'] = $request->has('moyen');

        $validated['lycee'] = $request->has('lycee');

        $validated['active'] = $request->has('active');

        // Ancien champ conservé pour compatibilité
        $validated['level'] = 'all';

        // Vérifier qu'au moins un cycle est sélectionné
        if (
            ! $validated['primaire'] &&
            ! $validated['moyen'] &&
            ! $validated['lycee']
        ) {
            return back()
                ->withErrors([
                    'cycle' => 'Veuillez sélectionner au moins un cycle scolaire.',
                ])
                ->withInput();
        }

        Subject::create($validated);

        return redirect()
            ->route('subjects.index')
            ->with(
                'success',
                'Matière ajoutée avec succès.'
            );
    }

    /**
     * Afficher une matière
     */
    public function show(Subject $subject)
    {
        return view(
            'subjects.show',
            compact('subject')
        );
    }

    /**
     * Formulaire de modification
     */
    public function edit(Subject $subject)
    {
        return view(
            'subjects.edit',
            compact('subject')
        );
    }

    /**
     * Mettre à jour une matière
     */
    public function update(
        Request $request,
        Subject $subject
    ) {
        $validated = $request->validate([
            'name' => 'required|string|max:100',

            'code' => 'required|string|max:50|unique:subjects,code,'.$subject->id,

            'description' => 'nullable|string',

            'primaire' => 'nullable|boolean',

            'moyen' => 'nullable|boolean',

            'lycee' => 'nullable|boolean',

            'hours_per_week' => 'required|integer|min:1|max:40',

            'active' => 'nullable|boolean',
        ]);

        // Cycles
        $validated['primaire'] =
            $request->has('primaire');

        $validated['moyen'] =
            $request->has('moyen');

        $validated['lycee'] =
            $request->has('lycee');

        // Statut
        $validated['active'] =
            $request->has('active');

        // Ancien champ conservé
        $validated['level'] = 'all';

        // Au moins un cycle
        if (
            ! $validated['primaire'] &&
            ! $validated['moyen'] &&
            ! $validated['lycee']
        ) {
            return back()
                ->withErrors([
                    'cycle' => 'Veuillez sélectionner au moins un cycle scolaire.',
                ])
                ->withInput();
        }

        $subject->update($validated);

        return redirect()
            ->route('subjects.index')
            ->with(
                'success',
                'Matière modifiée avec succès.'
            );
    }

    /**
     * Récupérer les enseignants d'une matière
     */
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
            ]);

        return response()->json($teachers);
    }

    /**
     * Supprimer une matière
     */
    public function destroy(Subject $subject)
    {
        // Ne jamais supprimer silencieusement des données métier liées
        $hasBusinessData = $subject->enrollments()->exists()
            || Attendance::where('subject_id', $subject->id)->exists()
            || ClassSession::where('subject_id', $subject->id)->exists()
            || Payment::where('subject_id', $subject->id)->exists()
            || PaymentSignalement::where('subject_id', $subject->id)->exists();

        if ($hasBusinessData) {

            return back()->with(
                'error',
                'Impossible de supprimer cette matière : elle est liée à des inscriptions, présences, séances, paiements ou signalements existants.'
            );
        }

        $subject->delete();

        return redirect()
            ->route('subjects.index')
            ->with(
                'success',
                'Matière supprimée avec succès.'
            );
    }

    /**
     * Formulaire gestion des enseignants
     */
    public function editTeachers(Subject $subject)
    {
        $teachers = Teacher::with('subjects')
            ->where(
                'active',
                true
            )
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $subject->load('teachers');

        return view(
            'subjects.teachers',
            compact('subject', 'teachers')
        );
    }

    /**
     * Mettre à jour les enseignants
     */
    public function updateTeachers(
        Request $request,
        Subject $subject
    ) {
        $validated = $request->validate([
            'teachers' => 'nullable|array',

            'teachers.*' => 'exists:teachers,id',
        ]);

        $subject->teachers()->sync(
            $validated['teachers'] ?? []
        );

        return redirect()
            ->route('subjects.index')
            ->with(
                'success',
                'Les enseignants de la matière ont été mis à jour avec succès.'
            );
    }
}
