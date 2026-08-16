<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ClassSessionController extends Controller
{
    /**
     * Liste des séances
     */
    public function index()
    {
        $sessions = ClassSession::with([
            'student',
            'subject',
            'teacher',
        ])
            ->latest()
            ->paginate(10);

        return view(
            'class-sessions.index',
            compact('sessions')
        );
    }

    /**
     * Formulaire d'ajout
     */
    public function create()
    {
        $students = Student::orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $subjects = Subject::where('active', true)
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $teachers = Teacher::where('active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view(
            'class-sessions.create',
            compact(
                'students',
                'subjects',
                'teachers'
            )
        );
    }

    /**
     * Enregistrer une séance
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day' => 'required|string|max:50',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
            'note' => 'nullable|string',
        ]);

        $isEnrolled = Enrollment::query()
            ->where('student_id', $validated['student_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('teacher_id', $validated['teacher_id'])
            ->where('status', 'active')
            ->exists();

        if (! $isEnrolled) {

            return back()
                ->withErrors([
                    'student_id' => "L'élève sélectionné n'est pas inscrit à cette matière avec cet enseignant.",
                ])
                ->withInput();
        }

        ClassSession::create($validated);

        return redirect()
            ->route('class-sessions.index')
            ->with(
                'success',
                'La séance a été ajoutée avec succès.'
            );
    }

    /**
     * Afficher une séance
     */
    public function show(ClassSession $classSession)
    {
        $classSession->load([
            'student',
            'subject',
            'teacher',
        ]);

        return view(
            'class-sessions.show',
            compact('classSession')
        );
    }

    /**
     * Formulaire de modification
     */
    public function edit(ClassSession $classSession)
    {
        $students = Student::orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $subjects = Subject::where('active', true)
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $teachers = Teacher::where('active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view(
            'class-sessions.edit',
            compact(
                'classSession',
                'students',
                'subjects',
                'teachers'
            )
        );
    }

    /**
     * Modifier une séance
     */
    public function update(
        Request $request,
        ClassSession $classSession
    ) {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day' => 'required|string|max:50',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
            'note' => 'nullable|string',
        ]);

        $classSession->update($validated);

        return redirect()
            ->route('class-sessions.index')
            ->with(
                'success',
                'La séance a été modifiée avec succès.'
            );
    }

    /**
     * Supprimer une séance
     */
    public function destroy(ClassSession $classSession)
    {
        $classSession->delete();

        return redirect()
            ->route('class-sessions.index')
            ->with(
                'success',
                'La séance a été supprimée avec succès.'
            );
    }
}
