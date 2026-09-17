<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
            // INSCRIPTIONS (basées sur les groupes)
            // =================================================

            'enrollments' => 'required|array|min:1',

            'enrollments.*.subject_id' => 'required|exists:subjects,id',

            'enrollments.*.group_id' => 'required|exists:groups,id',
        ], [
            'enrollments.required' => 'Veuillez ajouter au moins une matière.',

            'enrollments.min' => 'Veuillez ajouter au moins une matière.',

            'enrollments.*.subject_id.required' => 'Veuillez sélectionner une matière.',

            'enrollments.*.group_id.required' => 'Veuillez sélectionner un groupe.',

            'enrollments.*.group_id.exists' => 'Le groupe sélectionné est invalide.',
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
        // VÉRIFIER CHAQUE INSCRIPTION
        // =====================================================

        $schoolYearId = SchoolYear::defaultId();
        $groupIds = [];

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
            // VÉRIFIER LE GROUPE
            // =================================================

            $group = Group::where('id', $enrollment['group_id'])
                ->where('is_active', true)
                ->where('subject_id', $enrollment['subject_id'])
                ->where('level', $validated['level'])
                ->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId))
                ->with(['tariffs' => function ($q) {
                    $q->where('is_active', true);
                }])
                ->first();

            if (! $group) {

                return back()
                    ->withErrors([
                        "enrollments.$index.group_id" => "Le groupe sélectionné pour l'inscription "
                            .($index + 1)
                            ." n'est pas compatible avec la matière ou le niveau.",
                    ])
                    ->withInput();
            }

            $groupIds[] = $group->id;

            // =================================================
            // DOUBLON GROUPE (même groupe dans le formulaire)
            // =================================================

            $groupCounts = array_count_values($groupIds);

            if ($groupCounts[$group->id] > 1) {

                return back()
                    ->withErrors([
                        "enrollments.$index.group_id" => 'Ce groupe est déjà utilisé dans une autre inscription.',
                    ])
                    ->withInput();
            }
        }

        // =====================================================
        // CRÉER L'ÉLÈVE + INSCRIPTIONS + GROUPES
        // =====================================================

        $student = DB::transaction(function () use ($validated, $groupIds) {

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

            $startDate = now()->toDateString();
            $schoolYearId = SchoolYear::forDate($startDate)?->id;

            foreach ($validated['enrollments'] as $enrollment) {

                $group = Group::find($enrollment['group_id']);
                $tariff = $group->currentTariff;
                $paymentType = $tariff ? $this->computePaymentType($group, $tariff) : null;

                Enrollment::create([
                    'student_id' => $student->id,
                    'subject_id' => $group->subject_id,
                    'teacher_id' => $group->teacher_id,
                    'group_id' => $group->id,
                    'start_date' => $startDate,
                    'status' => 'active',
                    'school_year_id' => $schoolYearId,
                    'payment_type' => $paymentType,
                ]);
            }

            // =================================================
            // SYNCHRONISER LES GROUPES
            // =================================================

            foreach ($groupIds as $groupId) {
                $student->groups()->syncWithoutDetaching([
                    $groupId => [
                        'joined_at' => now()->toDateString(),
                        'is_active' => true,
                    ],
                ]);
            }

            return $student;
        });

        // =====================================================
        // NOTIFICATIONS PUSH INSCRIPTION
        // =====================================================

        try {
            $notificationService = app(FirebaseNotificationService::class);

            foreach ($validated['enrollments'] as $enrollmentData) {
                $subject = Subject::find($enrollmentData['subject_id']);
                $group = Group::with('teacher')->find($enrollmentData['group_id']);
                $teacher = $group?->teacher;

                if (! $student->user || ! $subject || ! $teacher) {
                    continue;
                }

                $studentName = $student->first_name.' '.$student->last_name;
                $subjectName = $subject->name;
                $teacherName = $teacher->first_name;

                $enrollment = Enrollment::where('student_id', $student->id)
                    ->where('subject_id', $enrollmentData['subject_id'])
                    ->where('teacher_id', $group->teacher_id)
                    ->where('status', 'active')
                    ->latest()
                    ->first();

                if ($enrollment) {
                    $notificationService->notifyEnrollment(
                        $student->user,
                        $studentName,
                        $subjectName,
                        $teacherName,
                        $student->id,
                        $enrollment->id
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Enrollment notification failed: '.$e->getMessage());
        }

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
            'enrollments.group.currentTariff',
            'enrollments.group.schedules',
            'enrollments.group.teacher',
            'enrollments.group.subject',
        ]);

        return view(
            'students.show',
            compact('student')
        );
    }

    /**
     * Calculer le type de paiement à partir du groupe et de son tarif.
     */
    private function computePaymentType(Group $group, GroupTariff $tariff): string
    {
        if ($group->mode === 'vip') {
            return $tariff->billing_type === 'monthly' ? 'vip_monthly' : 'vip_per_session';
        }

        if ($group->mode === 'special') {
            return 'special_monthly';
        }

        return 'monthly';
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
        $student->load([
            'enrollments.subject',
            'enrollments.teacher',
            'enrollments.group',
            'groups',
        ]);

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

            'password' => 'nullable|string|min:8',
        ]);

        // =====================================================
        // MOT DE PASSE : validation séparée + vérification
        // =====================================================

        $newPassword = null;

        if (! empty($validated['password'])) {

            $request->validate([
                'password_confirmation' => 'required|same:password',
            ], [
                'password_confirmation.required' => 'La confirmation du mot de passe est requise.',
                'password_confirmation.same' => 'Les mots de passe ne correspondent pas.',
            ]);

            if (! $student->user) {

                return back()
                    ->withErrors([
                        'password' => 'Cet élève ne possède pas encore de compte utilisateur. Veuillez d\'abord créer son compte.',
                    ])
                    ->withInput();
            }

            $newPassword = $validated['password'];
        }

        // Vérifier que le niveau appartient à un cycle connu
        $cycle = $this->getCycleFromLevel($validated['level']);

        if (! $cycle) {
            return back()
                ->withErrors([
                    'level' => 'Le niveau scolaire sélectionné est invalide.',
                ])
                ->withInput();
        }

        // Ne jamais transformer automatiquement une date existante en NULL
        if (empty($validated['date_of_birth']) && $student->date_of_birth !== null) {
            $validated['date_of_birth'] = $student->date_of_birth->toDateString();
        }

        $submittedEnrollments = $this->extractSubmittedEnrollments($request);

        // =====================================================
        // INSCRIPTIONS SOUMISES : VALIDATION + COMPATIBILITÉ
        // =====================================================

        $enrollmentGroupIds = [];
        $hasGroupKey = false;
        $validator = null;

        if ($submittedEnrollments !== []) {

            $validator = Validator::make($request->all(), [

                'enrollments' => 'required|array|min:1',

                'enrollments.*.subject_id' => 'required|exists:subjects,id',

                'enrollments.*.teacher_id' => 'required|exists:teachers,id',

                'enrollments.*.group_id' => 'nullable|exists:groups,id',
            ], [
                'enrollments.required' => 'Veuillez ajouter au moins une matière.',

                'enrollments.min' => 'Veuillez ajouter au moins une matière.',

                'enrollments.*.subject_id.required' => 'Veuillez sélectionner une matière.',

                'enrollments.*.teacher_id.required' => 'Veuillez sélectionner un enseignant.',

                'enrollments.*.subject_id.exists' => 'La matière sélectionnée est invalide.',

                'enrollments.*.teacher_id.exists' => 'L\'enseignant sélectionné est invalide.',

                'enrollments.*.group_id.exists' => 'Le groupe sélectionné est invalide.',
            ]);

            $enrollmentGroupIds = [];

            $hasGroupKey = collect($submittedEnrollments)->contains(fn ($row) => array_key_exists('group_id', $row));

            $validator->after(function ($validator) use ($submittedEnrollments, $validated, &$enrollmentGroupIds, $student) {
                $this->checkEnrollmentCompatibility(
                    $validator,
                    $submittedEnrollments,
                    $validated['level'],
                    $enrollmentGroupIds,
                    $student
                );
            });

            $validator->validate();
        } else {
            // =====================================================
            // AUCUNE INSCRIPTION SOUMISE :
            // VÉRIFIER LES INSCRIPTIONS ACTIVES PERSISTÉES
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

            // Extraire les groupes des inscriptions existantes
            $enrollmentGroupIds = $student->groups()
                ->wherePivot('is_active', true)
                ->pluck('groups.id')
                ->toArray();
        }

        // =====================================================
        // TRANSACTION : ÉLÈVE + INSCRIPTIONS + GROUPES
        // =====================================================

        DB::transaction(function () use ($student, $validated, $submittedEnrollments, $enrollmentGroupIds, $hasGroupKey, $validator, $newPassword) {

            $student->update($validated);

            // Mettre à jour le mot de passe si fourni
            if ($newPassword !== null && $student->user) {
                $student->user->update([
                    'password' => Hash::make($newPassword),
                ]);
            }

            if ($submittedEnrollments !== []) {
                $this->syncEnrollments($student, $submittedEnrollments, $validator);
            }

            // Synchroniser les groupes depuis les inscriptions.
            // Si des inscriptions ont été soumises mais aucun group_id
            // n'était présent (champs désactivés non soumis), on
            // préserve les associations groupes existantes.
            if ($submittedEnrollments === [] || $hasGroupKey) {
                $this->syncStudentGroups($student, $enrollmentGroupIds);
            }
        });

        return redirect()
            ->route('students.index')
            ->with(
                'success',
                'Élève modifié avec succès.'
            );
    }

    /**
     * Extraire les lignes d'inscription soumises par le formulaire.
     *
     * Retourne [] si aucun champ "enrollments" n'a été soumis.
     */
    private function extractSubmittedEnrollments(Request $request): array
    {
        $rows = (array) $request->input('enrollments', []);

        if ($rows === []) {
            return [];
        }

        return array_values(array_filter($rows, 'is_array'));
    }

    /**
     * Vérifier la compatibilité des inscriptions soumises
     * avec le niveau scolaire fourni.
     */
    private function checkEnrollmentCompatibility(
        $validator,
        array $enrollments,
        string $level,
        array &$enrollmentGroupIds,
        ?Student $student = null
    ): void {
        $cycle = $this->getCycleFromLevel($level);

        if (! $cycle) {
            $validator->errors()->add(
                'level',
                'Le niveau scolaire sélectionné est invalide.'
            );

            return;
        }

        $schoolYearId = SchoolYear::defaultId();
        $combinations = [];

        foreach ($enrollments as $index => $enrollment) {

            if (! is_array($enrollment)) {
                continue;
            }

            $subjectId = $enrollment['subject_id'] ?? null;
            $teacherId = $enrollment['teacher_id'] ?? null;
            $groupId = $enrollment['group_id'] ?? null;

            if (! $subjectId || ! $teacherId || ! $groupId) {
                continue;
            }

            // =================================================
            // DOUBLON GROUPE (même groupe dans le formulaire)
            // =================================================

            $groupKey = (string) $groupId;

            if (isset($combinations[$groupKey])) {

                $validator->errors()->add(
                    'enrollments',
                    'Ce groupe est déjà sélectionné pour une autre inscription.'
                );
            }

            $combinations[$groupKey] = true;

            // =================================================
            // DOUBLON GROUPE (inscription active existante)
            // =================================================

            if ($student) {
                $enrollmentId = $enrollment['id'] ?? null;

                // ── Nouvel enrollment (ID absent) : doublon actif → erreur ──

                if (! $enrollmentId) {
                    $existingActive = Enrollment::where('student_id', $student->id)
                        ->where('group_id', $groupId)
                        ->where('status', 'active')
                        ->exists();

                    if ($existingActive) {
                        $validator->errors()->add(
                            'enrollments',
                            'Cet élève est déjà inscrit dans ce groupe.'
                        );
                    }
                } else {
                    // ── Modification (ID présent) ──

                    $currentEnrollment = Enrollment::find($enrollmentId);

                    if ($currentEnrollment && $groupId) {
                        $groupChanged = (int) $currentEnrollment->group_id !== (int) $groupId;

                        // Changement de groupe + historique → INTERDIT
                        if ($groupChanged && $this->enrollmentHasHistory($currentEnrollment)) {
                            $validator->errors()->add(
                                'enrollments',
                                'Impossible de changer le groupe d\'une inscription avec historique. '
                                    .'Créez une nouvelle inscription pour le groupe souhaité.'
                            );
                        }

                        // Groupe occupé par un AUTRE → erreur
                        $occupied = Enrollment::where('student_id', $student->id)
                            ->where('group_id', $groupId)
                            ->where('id', '!=', $enrollmentId)
                            ->exists();

                        if ($occupied) {
                            $validator->errors()->add(
                                'enrollments',
                                'Cet élève est déjà inscrit dans ce groupe.'
                            );
                        }
                    }
                }
            }

            // =================================================
            // MATIÈRE ACTIVE + DU BON CYCLE
            // =================================================

            $subject = Subject::where('id', $subjectId)
                ->where('active', true)
                ->where($cycle, true)
                ->first();

            if (! $subject) {

                $validator->errors()->add(
                    "enrollments.$index.subject_id",
                    "La matière sélectionnée pour l'inscription "
                        .($index + 1)
                        ." n'est pas disponible pour le niveau "
                        .$level
                        .'.'
                );

                continue;
            }

            // =================================================
            // ENSEIGNANT RATTACHÉ + ACTIF + BON CYCLE
            // =================================================

            $cycleCode = $this->cycleCodeForLevel($level);

            $teacherExists = $subject->teachers()
                ->where('teachers.id', $teacherId)
                ->where('teachers.active', true)
                ->whereHas('levels', function ($query) use ($cycleCode) {
                    $query->where('code', $cycleCode)
                        ->where('active', true);
                })
                ->exists();

            if (! $teacherExists) {

                $validator->errors()->add(
                    "enrollments.$index.teacher_id",
                    "L'enseignant sélectionné pour l'inscription "
                        .($index + 1)
                        .' ne correspond pas à cette matière.'
                );

                continue;
            }

            // =================================================
            // GROUPE : ACTIF + BONNE MATIÈRE + BON NIVEAU
            // + BON ENSEIGNANT + BONNE ANNÉE SCOLAIRE
            // =================================================

            $group = Group::where('id', $groupId)
                ->where('is_active', true)
                ->where('subject_id', $subjectId)
                ->where('level', $level)
                ->where('teacher_id', $teacherId)
                ->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId))
                ->first();

            if (! $group) {

                $validator->errors()->add(
                    "enrollments.$index.group_id",
                    "Le groupe sélectionné pour l'inscription "
                        .($index + 1)
                        .' n\'est pas compatible avec la matière, l\'enseignant ou le niveau.'
                );

                continue;
            }

            // =================================================
            // LE GROUPE EST COMPATIBLE : COLLECTER L'ID
            // =================================================

            $enrollmentGroupIds[] = $group->id;
        }
    }

    /**
     * Synchroniser les inscriptions actives de l'élève
     * avec les lignes soumises par le formulaire.
     */
    private function syncEnrollments(
        Student $student,
        array $enrollments,
        $validator
    ): void {
        // =====================================================
        // COLLECTE DES INSCRIPTIONS ACTIVES
        // =====================================================

        $existing = $student->enrollments()
            ->where('status', 'active')
            ->get()
            ->keyBy('id');

        $submittedIds = [];

        foreach ($enrollments as $row) {

            if (! is_array($row)) {
                continue;
            }

            $id = (int) ($row['id'] ?? 0);

            if ($id > 0) {
                $submittedIds[] = $id;
            }
        }

        // =====================================================
        // INSCRIPTIONS RETIRÉES DU FORMULAIRE
        // =====================================================

        foreach ($existing as $enrollment) {

            if (in_array((int) $enrollment->id, $submittedIds, true)) {
                continue;
            }

            if ($this->enrollmentHasHistory($enrollment)) {
                $enrollment->update(['status' => 'inactive']);
            } else {
                $enrollment->delete();
            }
        }

        // =====================================================
        // UPSERT DES INSCRIPTIONS SOUMISES
        // =====================================================

        foreach ($enrollments as $row) {

            if (! is_array($row)) {
                continue;
            }

            $id = (int) ($row['id'] ?? 0);

            if (
                trim((string) ($row['subject_id'] ?? '')) === ''
            ) {
                continue;
            }

            $groupId = $row['group_id'] ?? null;
            $groupExplicitlyCleared = array_key_exists('group_id', $row) && $groupId === null;
            $group = $groupId ? Group::find($groupId) : null;
            $teacherId = $group ? $group->teacher_id : (int) ($row['teacher_id'] ?? 0);

            if (! $teacherId) {
                continue;
            }

            // ── BRANCHE 1 : ID PRÉSENT (modification d'un enrollment existant) ──

            if ($id > 0) {
                $current = $existing->get($id);

                if (! $current) {
                    continue;
                }

                $groupChanged = $group
                    && (int) $current->group_id !== (int) $group->id;

                // ── SAFEGUARD : historique + changement de groupe → INTERDIT ──

                if ($groupChanged && $this->enrollmentHasHistory($current)) {
                    $validator->errors()->add(
                        'enrollments',
                        'Impossible de changer le groupe d\'une inscription avec historique. '
                            .'Créez une nouvelle inscription pour le groupe souhaité.'
                    );

                    continue;
                }

                // ── Vérifier que le groupe cible n'est pas occupé ──

                if ($group) {
                    $occupied = Enrollment::where('student_id', $student->id)
                        ->where('group_id', $group->id)
                        ->where('id', '!=', $id)
                        ->exists();

                    if ($occupied) {
                        $validator->errors()->add(
                            'enrollments',
                            'Cet élève est déjà inscrit dans ce groupe.'
                        );

                        continue;
                    }
                }

                // ── Deriver payment_type depuis Group + GroupTariff ──

                $paymentType = $current->payment_type ?? 'monthly';

                if ($group) {
                    $tariff = $group->currentTariff;

                    if ($tariff) {
                        $paymentType = $this->computePaymentType($group, $tariff);
                    }
                }

                // ── Update sur place — même ID ──

                $updateData = ['status' => 'active'];

                if ($group) {
                    $updateData['group_id'] = $group->id;
                    $updateData['subject_id'] = $group->subject_id;
                    $updateData['teacher_id'] = $teacherId;
                    $updateData['payment_type'] = $paymentType;
                } elseif ($groupExplicitlyCleared) {
                    $updateData['group_id'] = null;
                }

                $current->update($updateData);

                continue;
            }

            // ── BRANCHE 2 : ID ABSENT (nouvel enrollment) ──

            if (! $group) {
                continue;
            }

            $paymentType = 'monthly';
            $tariff = $group->currentTariff;

            if ($tariff) {
                $paymentType = $this->computePaymentType($group, $tariff);
            }

            $existingForGroup = Enrollment::where('student_id', $student->id)
                ->where('group_id', $group->id)
                ->first();

            if ($existingForGroup) {

                if ($existingForGroup->status === 'active') {
                    $validator->errors()->add(
                        'enrollments',
                        'Cet élève est déjà inscrit dans ce groupe.'
                    );

                    continue;
                }

                // Réactiver le MÊME enrollment (même ID)
                $existingForGroup->update([
                    'status' => 'active',
                    'subject_id' => $group->subject_id,
                    'teacher_id' => $teacherId,
                    'payment_type' => $paymentType,
                ]);

                continue;
            }

            // Créer un nouvel enrollment
            Enrollment::create([
                'student_id' => $student->id,
                'subject_id' => $group->subject_id,
                'teacher_id' => $teacherId,
                'group_id' => $group->id,
                'start_date' => now()->toDateString(),
                'status' => 'active',
                'payment_type' => $paymentType,
                'school_year_id' => SchoolYear::forDate(now())?->id,
            ]);
        }
    }

    /**
     * Une inscription possède-t-elle un historique
     * (paiements ou présences) à préserver ?
     */
    private function enrollmentHasHistory(Enrollment $enrollment): bool
    {
        return $enrollment->student->payments()
            ->where('subject_id', $enrollment->subject_id)
            ->exists()
            || $enrollment->student->attendances()
                ->where('subject_id', $enrollment->subject_id)
                ->where('teacher_id', $enrollment->teacher_id)
                ->exists();
    }

    /**
     * Synchroniser les groupes d'un élève.
     *
     * Les groupes retirés de la sélection sont désactivés (is_active = false)
     * plutôt que supprimés, pour préserver l'historique.
     */
    private function syncStudentGroups(Student $student, array $groupIds): void
    {
        // Récupérer les associations actuelles
        $currentPivots = $student->groups()
            ->wherePivot('is_active', true)
            ->pluck('groups.id')
            ->toArray();

        $selectedIds = array_map('intval', $groupIds);

        // Désactiver les groupes retirés
        $toDeactivate = array_diff($currentPivots, $selectedIds);

        foreach ($toDeactivate as $groupId) {
            $student->groups()->updateExistingPivot($groupId, [
                'is_active' => false,
            ]);
        }

        // Activer / ajouter les groupes sélectionnés
        foreach ($selectedIds as $groupId) {
            $exists = $student->groups()->where('groups.id', $groupId)->first();

            if ($exists) {
                // Réactiver si désactivé
                $student->groups()->updateExistingPivot($groupId, [
                    'is_active' => true,
                ]);
            } else {
                // Nouvelle association
                $student->groups()->attach($groupId, [
                    'joined_at' => now()->toDateString(),
                    'is_active' => true,
                ]);
            }
        }
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
