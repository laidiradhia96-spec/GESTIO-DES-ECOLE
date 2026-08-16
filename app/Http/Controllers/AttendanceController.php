<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\PaymentSignalementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    /**
     * Liste des prÃ©sences
     */
    public function index(Request $request)
    {
        $query = Attendance::with([
            'student',
            'subject',
            'teacher',
        ]);

        // Recherche Ã©lÃ¨ve
        if ($request->filled('search')) {

            $search = trim($request->input('search'));

            $query->whereHas('student', function ($q) use ($search) {

                $q->search($search);
            });
        }

        // Filtre date
        if ($request->filled('date')) {

            $query->whereDate(
                'date',
                $request->input('date')
            );
        }

        // Filtre matiÃ¨re
        if ($request->filled('subject_id')) {

            $query->where(
                'subject_id',
                $request->input('subject_id')
            );
        }

        // Filtre statut
        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->input('status')
            );
        }

        $attendances = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $subjects = Subject::orderBy('name')->get();

        return view(
            'attendances.index',
            compact(
                'attendances',
                'subjects'
            )
        );
    }

    /**
     * Formulaire pour enregistrer une sÃ©ance
     */
    public function create()
    {
        $subjects = Subject::query()
            ->orderBy('name')
            ->get();

        $teachers = Teacher::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view(
            'attendances.create',
            compact(
                'subjects',
                'teachers'
            )
        );
    }

    /**
     * Récupérer les élèves inscrits (enrollment actif)
     * à une matière + un enseignant.
     */
    public function students(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
            ],

            'teacher_id' => [
                'required',
                'integer',
                'exists:teachers,id',
            ],
        ]);

        $students = Student::query()
            ->whereIn(
                'id',
                $this->enrolledStudentIds(
                    $validated['subject_id'],
                    $validated['teacher_id']
                )
            )
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get([
                'id',
                'first_name',
                'last_name',
            ]);

        return response()->json($students);
    }

    /**
     * Enregistrer une sÃ©ance
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => [
                'required',
                'date',
            ],

            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
            ],

            'teacher_id' => [
                'required',
                'integer',
                'exists:teachers,id',
            ],

            'students' => [
                'required',
                'array',
                'min:1',
            ],

            'students.*.id' => [
                'required',
                'integer',
                Rule::in(
                    $this->enrolledStudentIds(
                        (int) $request->input('subject_id'),
                        (int) $request->input('teacher_id')
                    )
                ),
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
            'students.*.id.in' => 'Un élève sélectionné n\'est pas inscrit à cette matière avec cet enseignant.',
        ]);

        DB::transaction(function () use ($validated) {

            foreach ($validated['students'] as $studentData) {

                /*
                |--------------------------------------------------------------------------
                | ENREGISTRER LA PRÃ‰SENCE
                |--------------------------------------------------------------------------
                */

                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $studentData['id'],

                        'subject_id' => $validated['subject_id'],

                        'date' => $validated['date'],
                    ],
                    [
                        'teacher_id' => $validated['teacher_id'] ?? null,

                        'status' => $studentData['status'],

                        'note' => $studentData['note'] ?? null,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | VÃ‰RIFICATION DU PAIEMENT
                |--------------------------------------------------------------------------
                |
                | Seulement pour :
                |
                | present
                | late
                |
                */

                if (
                    in_array(
                        $studentData['status'],
                        ['present', 'late']
                    )
                ) {

                    $this->checkPaymentForAttendance(
                        $attendance
                    );
                }
            }
        });

        return redirect()
            ->route('attendances.index')
            ->with(
                'success',
                'Les prÃ©sences ont Ã©tÃ© enregistrÃ©es avec succÃ¨s.'
            );
    }

    /**
     * Afficher les dÃ©tails d'une sÃ©ance
     */
    public function show(Attendance $attendance)
    {
        $attendance->load([
            'student',
            'subject',
            'teacher',
        ]);

        $attendances = Attendance::with([
            'student',
            'subject',
            'teacher',
        ])
            ->where(
                'date',
                $attendance->date
            )
            ->where(
                'subject_id',
                $attendance->subject_id
            )
            ->orderBy('id')
            ->get();

        return view(
            'attendances.show',
            compact(
                'attendance',
                'attendances'
            )
        );
    }

    /**
     * Formulaire de modification
     */
    public function edit(Attendance $attendance)
    {
        $attendance->load([
            'student',
            'subject',
            'teacher',
        ]);

        $attendances = Attendance::with([
            'student',
            'subject',
            'teacher',
        ])
            ->where(
                'date',
                $attendance->date
            )
            ->where(
                'subject_id',
                $attendance->subject_id
            )
            ->orderBy('id')
            ->get();

        $students = Student::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $subjects = Subject::query()
            ->orderBy('name')
            ->get();

        $teachers = Teacher::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view(
            'attendances.edit',
            compact(
                'attendance',
                'attendances',
                'students',
                'subjects',
                'teachers'
            )
        );
    }

    /**
     * Mise Ã  jour des prÃ©sences
     */
    public function update(
        Request $request,
        Attendance $attendance
    ) {

        $validated = $request->validate([

            'date' => [
                'required',
                'date',
            ],

            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
            ],

            'teacher_id' => [
                'required',
                'integer',
                'exists:teachers,id',
            ],

            'students' => [
                'required',
                'array',
                'min:1',
            ],

            'students.*.id' => [
                'required',
                'integer',
                Rule::in(
                    $this->enrolledStudentIds(
                        (int) $request->input('subject_id'),
                        (int) $request->input('teacher_id')
                    )
                ),
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
            'students.*.id.in' => 'Un élève sélectionné n\'est pas inscrit à cette matière avec cet enseignant.',
        ]);

        DB::transaction(function () use (
            $validated,
            $attendance
        ) {

            $oldDate =
                $attendance->date;

            $oldSubjectId =
                $attendance->subject_id;

            /*
            |--------------------------------------------------------------------------
            | Mise Ã  jour / crÃ©ation
            |--------------------------------------------------------------------------
            */

            foreach ($validated['students'] as $student) {

                $updatedAttendance =
                    Attendance::updateOrCreate(
                        [
                            'student_id' => $student['id'],

                            'subject_id' => $validated['subject_id'],

                            'date' => $validated['date'],
                        ],
                        [
                            'teacher_id' => $validated['teacher_id'] ?? null,

                            'status' => $student['status'],

                            'note' => $student['note'] ?? null,
                        ]
                    );

                /*
                |--------------------------------------------------------------------------
                | VÃ©rification paiement aprÃ¨s modification
                |--------------------------------------------------------------------------
                */

                if (
                    in_array(
                        $student['status'],
                        ['present', 'late']
                    )
                ) {

                    $this->checkPaymentForAttendance(
                        $updatedAttendance
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Supprimer les anciennes prÃ©sences
            |--------------------------------------------------------------------------
            */

            Attendance::where(
                'date',
                $oldDate
            )
                ->where(
                    'subject_id',
                    $oldSubjectId
                )
                ->whereNotIn(
                    'student_id',
                    collect(
                        $validated['students']
                    )
                        ->pluck('id')
                        ->toArray()
                )
                ->delete();
        });

        return redirect()
            ->route('attendances.index')
            ->with(
                'success',
                'Les prÃ©sences ont Ã©tÃ© modifiÃ©es avec succÃ¨s.'
            );
    }

    /**
     * Supprimer une prÃ©sence
     */
    public function destroy(
        Attendance $attendance
    ) {

        $studentId = $attendance->student_id;

        $subjectId = $attendance->subject_id;

        $date = $attendance->date
            ? $attendance->date->toDateString()
            : null;

        $attendance->delete();

        // Réconcilier la dette : supprimer le signalement ouvert
        // uniquement si plus aucune présence ne le justifie.
        if ($studentId && $subjectId && $date) {

            app(PaymentSignalementService::class)
                ->reconcileAfterAttendanceRemoval(
                    $studentId,
                    $subjectId,
                    $date
                );
        }

        return redirect()
            ->route('attendances.index', request()->query())
            ->with(
                'success',
                'La prÃ©sence a Ã©tÃ© supprimÃ©e avec succÃ¨s.'
            );
    }

    /**
     * Impression d'une sÃ©ance
     */
    public function print(
        Attendance $attendance
    ) {

        $attendance->load([
            'student',
            'subject',
            'teacher',
        ]);

        $attendances = Attendance::with([
            'student',
            'subject',
            'teacher',
        ])
            ->where(
                'date',
                $attendance->date
            )
            ->where(
                'subject_id',
                $attendance->subject_id
            )
            ->orderBy('id')
            ->get();

        return view(
            'attendances.print',
            compact(
                'attendance',
                'attendances'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ÉLÈVES INSCRITS (ENROLLMENT ACTIF)
    |--------------------------------------------------------------------------
    */

    /**
     * IDs des élèves ayant un enrollment actif
     * pour une matière + un enseignant.
     *
     * Source unique de vérité utilisée à la fois par
     * l'endpoint AJAX et par la validation serveur.
     */
    private function enrolledStudentIds(
        int $subjectId,
        int $teacherId
    ): array {

        return Enrollment::query()
            ->where('subject_id', $subjectId)
            ->where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->pluck('student_id')
            ->unique()
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | VÉRIFICATION DU PAIEMENT
    |--------------------------------------------------------------------------
    */

    private function checkPaymentForAttendance(
        Attendance $attendance
    ): void {

        app(PaymentSignalementService::class)
            ->syncFromAttendance($attendance);
    }
}
