<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\FirebaseNotificationService;
use App\Services\PaymentSignalementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    /**
     * Liste des présences
     */
    public function index(Request $request)
    {
        $query = Attendance::query();

        // Filtre année scolaire
        $schoolYears = SchoolYear::orderByDesc('start_date')->get();

        $schoolYearId = $request->filled('school_year_id')
            ? (int) $request->school_year_id
            : ($request->has('school_year_id')
                ? null
                : SchoolYear::defaultId());

        $query->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId));

        // Filtre date
        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        // Filtre matière
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        // Filtre groupe
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->input('group_id'));
        }

        // Filtre enseignant
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->input('teacher_id'));
        }

        // Filtre statut
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // =====================================================
        // GROUPEMENT PAR SÉANCE (teacher + subject + group + date)
        // =====================================================

        $sessions = $query
            ->select([
                'teacher_id',
                'subject_id',
                'group_id',
                'date',
                DB::raw('COUNT(DISTINCT student_id) as student_count'),
                DB::raw('MAX(id) as latest_id'),
            ])
            ->groupBy('teacher_id', 'subject_id', 'group_id', 'date')
            ->orderByDesc('date')
            ->orderByDesc('latest_id')
            ->paginate(15)
            ->withQueryString();

        // Charger les relations pour chaque session
        $sessionIds = $sessions->pluck('latest_id')->toArray();
        $attendanceMap = Attendance::with(['teacher', 'subject', 'group'])
            ->whereIn('id', $sessionIds)
            ->get()
            ->keyBy('id');

        // Transformer les sessions pour la vue
        $sessions->getCollection()->transform(function ($session) use ($attendanceMap) {
            $ref = $attendanceMap->get($session->latest_id);
            $session->teacher = $ref?->teacher;
            $session->subject = $ref?->subject;
            $session->group = $ref?->group;
            $session->ref_attendance = $ref;

            return $session;
        });

        $subjects = Subject::orderBy('name')->get();
        $groups = Group::where('is_active', true)->orderBy('name')->get();
        $teachers = Teacher::where('active', true)->orderBy('last_name')->orderBy('first_name')->get();

        return view('attendances.index', compact(
            'sessions',
            'subjects',
            'groups',
            'teachers',
            'schoolYears',
            'schoolYearId',
        ));
    }

    /**
     * Formulaire pour enregistrer une séance
     */
    public function create()
    {
        $teachers = Teacher::where('active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('attendances.create', compact('teachers'));
    }

    /**
     * AJAX : Groupes d'un enseignant
     *
     * Retourne les groupes actifs de l'enseignant avec matière et niveau.
     */
    public function groupsByTeacher(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
        ]);

        $groups = Group::where('teacher_id', $validated['teacher_id'])
            ->where('is_active', true)
            ->with(['subject:id,name', 'schoolYear:id,name'])
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        return response()->json($groups);
    }

    /**
     * AJAX : Élèves d'un groupe
     *
     * Retourne les élèves ayant une inscription active dans le groupe donné.
     */
    public function studentsByGroup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'group_id' => ['required', 'integer', 'exists:groups,id'],
        ]);

        $students = Student::query()
            ->whereHas('enrollments', function ($q) use ($validated) {
                $q->where('group_id', $validated['group_id'])
                    ->where('status', 'active');
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['students.id', 'first_name', 'last_name']);

        return response()->json($students);
    }

    /**
     * Enregistrer une séance
     */
    public function store(Request $request)
    {
        $groupId = (int) $request->input('group_id');

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],

            'students' => ['required', 'array', 'min:1'],

            'students.*.id' => [
                'required',
                'integer',
                Rule::in($this->groupStudentIds($groupId)),
            ],

            'students.*.status' => [
                'required',
                'in:present,absent,late,justified',
            ],

            'students.*.note' => [
                'nullable',
                'string',
            ],
        ], [
            'group_id.required' => 'Veuillez sélectionner un groupe.',
            'group_id.exists' => 'Le groupe sélectionné est invalide.',
            'students.*.id.in' => 'Un élève sélectionné n\'est pas inscrit à ce groupe.',
        ]);

        DB::transaction(function () use ($validated, $groupId) {

            foreach ($validated['students'] as $studentData) {

                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $studentData['id'],
                        'subject_id' => $validated['subject_id'],
                        'group_id' => $groupId,
                        'date' => $validated['date'],
                    ],
                    [
                        'teacher_id' => $validated['teacher_id'] ?? null,
                        'status' => $studentData['status'],
                        'note' => $studentData['note'] ?? null,
                        'school_year_id' => SchoolYear::forDate($validated['date'])?->id,
                    ]
                );

                if (in_array($studentData['status'], ['present', 'late'])) {
                    $this->checkPaymentForAttendance($attendance);
                }
            }
        });

        // =====================================================
        // NOTIFICATIONS PUSH
        // =====================================================

        try {
            $notificationService = app(FirebaseNotificationService::class);
            $subject = Subject::find($validated['subject_id']);

            foreach ($validated['students'] as $studentData) {
                $student = Student::find($studentData['id']);
                if (! $student || ! $student->user) {
                    continue;
                }

                $studentName = $student->first_name.' '.$student->last_name;
                $subjectName = $subject?->name ?? '—';

                $latestAttendance = Attendance::where('student_id', $student->id)
                    ->where('subject_id', $validated['subject_id'])
                    ->where('group_id', $groupId)
                    ->whereDate('date', $validated['date'])
                    ->first();

                if (! $latestAttendance) {
                    continue;
                }

                $status = $studentData['status'];

                if ($status === 'present') {
                    $notificationService->notifyPresence(
                        $student->user,
                        $studentName,
                        $subjectName,
                        $student->id,
                        $latestAttendance->id
                    );
                } elseif ($status === 'absent') {
                    $notificationService->notifyAbsence(
                        $student->user,
                        $studentName,
                        $subjectName,
                        $student->id,
                        $latestAttendance->id
                    );
                } elseif ($status === 'late') {
                    $notificationService->notifyRetard(
                        $student->user,
                        $studentName,
                        $subjectName,
                        $student->id,
                        $latestAttendance->id
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Attendance notification failed: '.$e->getMessage());
        }

        return redirect()
            ->route('attendances.index')
            ->with('success', 'Les présences ont été enregistrées avec succès.');
    }

    /**
     * Afficher les détails d'une séance
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['student', 'subject', 'teacher', 'group']);

        $attendances = Attendance::with(['student', 'subject', 'teacher', 'group'])
            ->where('date', $attendance->date)
            ->where('subject_id', $attendance->subject_id)
            ->where('group_id', $attendance->group_id)
            ->orderBy('id')
            ->get();

        return view('attendances.show', compact('attendance', 'attendances'));
    }

    /**
     * Formulaire de modification
     */
    public function edit(Attendance $attendance)
    {
        $attendance->load(['student', 'subject', 'teacher', 'group']);

        $attendances = Attendance::with(['student', 'subject', 'teacher', 'group'])
            ->where('date', $attendance->date)
            ->where('subject_id', $attendance->subject_id)
            ->where('group_id', $attendance->group_id)
            ->orderBy('id')
            ->get();

        $students = Student::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $subjects = Subject::query()->orderBy('name')->get();

        $teachers = Teacher::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $groups = Group::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('attendances.edit', compact(
            'attendance',
            'attendances',
            'students',
            'subjects',
            'teachers',
            'groups',
        ));
    }

    /**
     * Mise à jour des présences
     */
    public function update(Request $request, Attendance $attendance)
    {
        $groupId = (int) $request->input('group_id');

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],

            'students' => ['required', 'array', 'min:1'],

            'students.*.id' => [
                'required',
                'integer',
                Rule::in($this->groupStudentIds($groupId)),
            ],

            'students.*.status' => [
                'required',
                'in:present,absent,late,justified',
            ],

            'students.*.note' => [
                'nullable',
                'string',
            ],
        ], [
            'students.*.id.in' => 'Un élève sélectionné n\'est pas inscrit à ce groupe.',
        ]);

        DB::transaction(function () use ($validated, $attendance, $groupId) {

            $oldDate = $attendance->date;
            $oldSubjectId = $attendance->subject_id;
            $oldGroupId = $attendance->group_id;

            foreach ($validated['students'] as $student) {

                $updatedAttendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $student['id'],
                        'subject_id' => $validated['subject_id'],
                        'group_id' => $groupId,
                        'date' => $validated['date'],
                    ],
                    [
                        'teacher_id' => $validated['teacher_id'] ?? null,
                        'status' => $student['status'],
                        'note' => $student['note'] ?? null,
                    ]
                );

                if (in_array($student['status'], ['present', 'late'])) {
                    $this->checkPaymentForAttendance($updatedAttendance);
                }
            }

            // Supprimer les anciennes présences non soumises
            Attendance::where('date', $oldDate)
                ->where('subject_id', $oldSubjectId)
                ->where('group_id', $oldGroupId)
                ->whereNotIn('student_id', collect($validated['students'])->pluck('id')->toArray())
                ->delete();
        });

        return redirect()
            ->route('attendances.index')
            ->with('success', 'Les présences ont été modifiées avec succès.');
    }

    /**
     * Supprimer une présence
     */
    public function destroy(Attendance $attendance)
    {
        $studentId = $attendance->student_id;
        $subjectId = $attendance->subject_id;
        $groupId = $attendance->group_id;
        $date = $attendance->date ? $attendance->date->toDateString() : null;

        $attendance->delete();

        if ($studentId && $subjectId && $date) {
            app(PaymentSignalementService::class)
                ->reconcileAfterAttendanceRemoval($studentId, $subjectId, $date, $groupId);
        }

        return redirect()
            ->route('attendances.index', request()->query())
            ->with('success', 'La présence a été supprimée avec succès.');
    }

    /**
     * Impression d'une séance
     */
    public function print(Attendance $attendance)
    {
        $attendance->load(['student', 'subject', 'teacher', 'group']);

        $attendances = Attendance::with(['student', 'subject', 'teacher', 'group'])
            ->where('date', $attendance->date)
            ->where('subject_id', $attendance->subject_id)
            ->where('group_id', $attendance->group_id)
            ->orderBy('id')
            ->get();

        return view('attendances.print', compact('attendance', 'attendances'));
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * IDs des élèves inscrits actifs dans un groupe.
     *
     * Source unique de vérité : Enrollment.group_id + Enrollment.status.
     */
    private function groupStudentIds(int $groupId): array
    {
        return Enrollment::where('group_id', $groupId)
            ->where('status', 'active')
            ->pluck('student_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * IDs des élèves ayant un enrollment actif (legacy).
     */
    private function enrolledStudentIds(int $subjectId, int $teacherId): array
    {
        return Enrollment::query()
            ->where('subject_id', $subjectId)
            ->where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->pluck('student_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Vérification du paiement
     */
    private function checkPaymentForAttendance(Attendance $attendance): void
    {
        app(PaymentSignalementService::class)
            ->syncFromAttendance($attendance);
    }
}
