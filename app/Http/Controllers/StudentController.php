<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    /**
     * Liste des élèves
     */
    public function index(Request $request)
    {
        $query = Student::query();

        // Filtre année scolaire (appartenance via les inscriptions)
        $schoolYears = SchoolYear::orderByDesc('start_date')->get();

        $schoolYearId = $request->filled('school_year_id')
            ? (int) $request->school_year_id
            : ($request->has('school_year_id')
                ? null
                : SchoolYear::defaultId());

        $query->when($schoolYearId, fn ($q) => $q->whereHas('enrollments', fn ($e) => $e->where('school_year_id', $schoolYearId)));

        if ($request->filled('search')) {

            $query->search($request->search);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        $students = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('students.index', compact('students', 'schoolYears', 'schoolYearId'));
    }

    /**
     * Liste des comptes élèves
     */
    public function accounts()
    {
        $students = Student::with('user')
            ->latest()
            ->paginate(10);

        return view('students.accounts', compact('students'));
    }

    /**
     * Formulaire d'ajout
     */
    public function create()
    {
        return view('students.create');
    }

    /**
     * Déterminer le cycle à partir du niveau scolaire
     */
    private function getCycleFromLevel(string $level): ?string
    {
        $level = strtoupper(trim($level));

        if (in_array($level, [
            '1AP',
            '2AP',
            '3AP',
            '4AP',
            '5AP',
        ])) {
            return 'primaire';
        }

        if (in_array($level, [
            '1AM',
            '2AM',
            '3AM',
            '4AM',
        ])) {
            return 'moyen';
        }

        if (in_array($level, [
            '1AS',
            '2AS',
            '3AS',
        ])) {
            return 'lycee';
        }

        return null;
    }

    /**
     * Code du cycle dans la table levels (PRI / MOY / SEC).
     *
     * Les codes 1AP, 2AM... sont des niveaux scolaires, pas des cycles.
     */
    private function cycleCodeForLevel(string $level): ?string
    {
        return match ($this->getCycleFromLevel($level)) {
            'primaire' => 'PRI',
            'moyen' => 'MOY',
            'lycee' => 'SEC',
            default => null,
        };
    }

    /**
     * Récupérer les matières selon le niveau scolaire
     *
     * Exemple :
     * 3AM -> Moyen -> matières où moyen = true
     */
    public function getSubjectsByLevel($level)
    {
        $cycle = $this->getCycleFromLevel($level);

        if (! $cycle) {
            return response()->json([
                'message' => 'Niveau scolaire invalide.',
            ], 422);
        }

        $subjects = Subject::where('active', true)
            ->where($cycle, true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'level',
                'primaire',
                'moyen',
                'lycee',
            ]);

        return response()->json($subjects);
    }

    /**
     * Récupérer les enseignants selon la matière
     */
    /**
     * Récupérer les enseignants d'une matière
     * selon le niveau scolaire de l'élève
     */
    public function getTeachersBySubject(Request $request, Subject $subject)
    {
        $level = $request->query('level');

        if (! $level) {
            return response()->json([
                'message' => 'Le niveau scolaire est obligatoire.',
            ], 422);
        }

        $level = strtoupper(trim($level));

        // Code du cycle réel : la table levels contient PRI / MOY / SEC.
        $cycle = $this->getCycleFromLevel($level);

        $cycleCode = $this->cycleCodeForLevel($level);

        if (! $cycleCode) {
            return response()->json([
                'message' => 'Niveau scolaire invalide.',
            ], 422);
        }

        if (! $subject->active || ! $subject->{$cycle}) {
            return response()->json([]);
        }

        /*
         * نجيب فقط الأساتذة:
         *
         * 1. مربوطين بالمادة
         * 2. Active
         * 3. عندهم Niveau داخل نفس Cycle
         */
        $teachers = $subject->teachers()
            ->where('teachers.active', true)
            ->whereHas('levels', function ($query) use ($cycleCode) {
                $query->where('code', $cycleCode)
                    ->where('active', true);
            })
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
     * Enregistrer un nouvel élève
     */
    public function store(Request $request)
    {
        // =====================================================
        // VALIDATION ÉLÈVE
        // =====================================================

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',

            'date_of_birth' => 'nullable|date',

            'phone' => 'nullable|string|max:30',

            'address' => 'nullable|string',

            'level' => 'required|string|max:100',

            'parent_name' => 'nullable|string|max:150',

            'parent_phone' => 'nullable|string|max:30',

            // =================================================
            // INSCRIPTIONS
            // =================================================

            'enrollments' => 'required|array|min:1',

            'enrollments.*.subject_id' => 'required|exists:subjects,id',

            'enrollments.*.teacher_id' => 'required|exists:teachers,id',

            'enrollments.*.payment_type' => 'required|string|max:50',
        ], [
            'enrollments.required' => 'Veuillez ajouter au moins une matière.',

            'enrollments.min' => 'Veuillez ajouter au moins une matière.',

            'enrollments.*.subject_id.required' => 'Veuillez sélectionner une matière.',

            'enrollments.*.teacher_id.required' => 'Veuillez sélectionner un enseignant.',

            'enrollments.*.payment_type.required' => 'Veuillez sélectionner un type d’abonnement.',
        ]);

        // =====================================================
        // DÉTERMINER LE CYCLE
        // =====================================================

        $cycle = $this->getCycleFromLevel($validated['level']);

        if (! $cycle) {
            return back()
                ->withErrors([
                    'level' => 'Le niveau scolaire sélectionné est invalide.',
                ])
                ->withInput();
        }

        // =====================================================
        // VÉRIFIER LES DOUBLONS MATIÈRE + ENSEIGNANT
        // =====================================================

        $combinations = [];

        foreach ($validated['enrollments'] as $enrollment) {

            $key = $enrollment['subject_id'].'-'.$enrollment['teacher_id'];

            if (isset($combinations[$key])) {

                return back()
                    ->withErrors([
                        'enrollments' => 'Cet enseignant est déjà sélectionné pour cette matière.',
                    ])
                    ->withInput();
            }

            $combinations[$key] = true;
        }

        // =====================================================
        // VÉRIFIER CHAQUE INSCRIPTION
        // =====================================================

        foreach ($validated['enrollments'] as $index => $enrollment) {

            // =================================================
            // VÉRIFIER LA MATIÈRE
            // =================================================

            $subject = Subject::where('id', $enrollment['subject_id'])
                ->where('active', true)
                ->where($cycle, true)
                ->first();

            if (! $subject) {

                return back()
                    ->withErrors([
                        "enrollments.$index.subject_id" => "La matière sélectionnée pour l'inscription "
                            .($index + 1)
                            ." n'est pas disponible pour le niveau "
                            .$validated['level']
                            .'.',
                    ])
                    ->withInput();
            }

            // =================================================
            // VÉRIFIER L'ENSEIGNANT
            // =================================================

            $cycleCode = $this->cycleCodeForLevel($validated['level']);

            $teacherExists = $subject->teachers()
                ->where('teachers.id', $enrollment['teacher_id'])
                ->where('teachers.active', true)
                ->whereHas('levels', function ($query) use ($cycleCode) {
                    $query->where('code', $cycleCode)
                        ->where('active', true);
                })
                ->exists();

            if (! $teacherExists) {

                return back()
                    ->withErrors([
                        "enrollments.$index.teacher_id" => "L'enseignant sélectionné pour l'inscription "
                            .($index + 1)
                            .' ne correspond pas à cette matière.',
                    ])
                    ->withInput();
            }
        }

        // =====================================================
        // CRÉER L'ÉLÈVE + INSCRIPTIONS
        // =====================================================

        $student = DB::transaction(function () use ($validated) {

            $student = Student::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'level' => $validated['level'],
                'parent_name' => $validated['parent_name'] ?? null,
                'parent_phone' => $validated['parent_phone'] ?? null,
            ]);

            foreach ($validated['enrollments'] as $enrollment) {

                $startDate = now()->toDateString();

                Enrollment::create([
                    'student_id' => $student->id,
                    'subject_id' => $enrollment['subject_id'],
                    'teacher_id' => $enrollment['teacher_id'],
                    'start_date' => $startDate,
                    'status' => 'active',
                    'payment_type' => $enrollment['payment_type'],
                    'school_year_id' => SchoolYear::forDate($startDate)?->id,
                ]);
            }

            return $student;
        });

        // =====================================================
        // REDIRECTION
        // =====================================================

        return redirect()
            ->route('students.index')
            ->with(
                'success',
                'Élève et inscriptions ajoutés avec succès.'
            );
    }

    /**
     * Afficher un élève
     */
    public function show(Student $student)
    {
        $student->load([
            'enrollments.subject',
            'enrollments.teacher',
        ]);

        return view(
            'students.show',
            compact('student')
        );
    }

    /**
     * Formulaire création compte
     */
    public function createAccount(Student $student)
    {
        if ($student->user) {

            return redirect()
                ->route('students.index')
                ->with(
                    'error',
                    'Cet élève possède déjà un compte.'
                );
        }

        return view(
            'students.create-account',
            compact('student')
        );
    }

    /**
     * Créer compte utilisateur
     */
    public function storeAccount(
        Request $request,
        Student $student
    ) {
        if ($student->user) {

            return redirect()
                ->route('students.index')
                ->with(
                    'error',
                    'Cet élève possède déjà un compte.'
                );
        }

        $validated = $request->validate([

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $user = User::create([

            'name' => $student->first_name
                .' '
                .$student->last_name,

            'email' => $validated['email'],

            'password' => Hash::make($validated['password']),

            'role' => 'student',
        ]);

        $student->update([
            'user_id' => $user->id,
        ]);

        return redirect()
            ->route('students.accounts')
            ->with(
                'success',
                'Le compte élève a été créé avec succès.'
            );
    }

    /**
     * Formulaire modification
     */
    public function edit(Student $student)
    {
        return view(
            'students.edit',
            compact('student')
        );
    }

    /**
     * Mettre à jour un élève
     */
    public function update(
        Request $request,
        Student $student
    ) {
        $validated = $request->validate([

            'first_name' => 'required|string|max:100',

            'last_name' => 'required|string|max:100',

            'date_of_birth' => 'nullable|date',

            'phone' => 'nullable|string|max:30',

            'address' => 'nullable|string',

            'level' => 'required|string|max:100',

            'parent_name' => 'nullable|string|max:150',

            'parent_phone' => 'nullable|string|max:30',
        ]);

        // Vérifier que le niveau appartient à un cycle connu
        $cycle = $this->getCycleFromLevel($validated['level']);

        if (! $cycle) {
            return back()
                ->withErrors([
                    'level' => 'Le niveau scolaire sélectionné est invalide.',
                ])
                ->withInput();
        }

        // =====================================================
        // CHANGEMENT DE NIVEAU : VÉRIFIER LES INSCRIPTIONS ACTIVES
        // =====================================================

        if (
            strtoupper(trim((string) $student->level))
            !== strtoupper(trim($validated['level']))
        ) {

            $cycleCode = $this->cycleCodeForLevel($validated['level']);

            $incompatible = [];

            $enrollments = $student->enrollments()
                ->where('status', 'active')
                ->with(['subject', 'teacher'])
                ->get();

            foreach ($enrollments as $enrollment) {

                $subject = $enrollment->subject;
                $teacher = $enrollment->teacher;

                $subjectCompatible = $subject
                    && $subject->active
                    && $subject->{$cycle};

                $teacherCompatible = false;

                if ($subjectCompatible && $teacher && $teacher->active) {

                    $teacherCompatible = $subject->teachers()
                        ->where('teachers.id', $teacher->id)
                        ->where('teachers.active', true)
                        ->whereHas('levels', function ($query) use ($cycleCode) {
                            $query->where('code', $cycleCode)
                                ->where('active', true);
                        })
                        ->exists();
                }

                if (! $subjectCompatible || ! $teacherCompatible) {

                    $subjectName = $subject ? $subject->name : '—';

                    $teacherName = $teacher
                        ? $teacher->first_name.' '.$teacher->last_name
                        : '—';

                    $incompatible[] = $subjectName.' ('.$teacherName.')';
                }
            }

            if ($incompatible !== []) {

                return back()
                    ->withErrors([
                        'level' => 'Impossible de changer le niveau : les inscriptions suivantes ne sont pas compatibles avec le niveau '
                            .$validated['level']
                            .' : '
                            .implode(', ', $incompatible)
                            .'.',
                    ])
                    ->withInput();
            }
        }

        $student->update($validated);

        return redirect()
            ->route('students.index')
            ->with(
                'success',
                'Élève modifié avec succès.'
            );
    }

    /**
     * Supprimer un élève
     */
    public function destroy(Student $student)
    {
        // Ne jamais supprimer silencieusement l'historique
        $hasHistory = $student->payments()->exists()
            || $student->attendances()->exists()
            || $student->enrollments()->exists();

        if ($hasHistory) {

            return back()
                ->withErrors([
                    'student' => 'Impossible de supprimer cet élève car il possède des données historiques (paiements, présences ou inscriptions).',
                ]);
        }

        // Aucun historique : suppression contrôlée de l'élève
        // et de son éventuel compte utilisateur lié.
        DB::transaction(function () use ($student) {

            $student->user()->delete();

            $student->delete();
        });

        return redirect()
            ->route('students.index')
            ->with(
                'success',
                'Élève supprimé avec succès.'
            );
    }
}
