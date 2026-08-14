<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\PaymentSignalement;

class AttendanceController extends Controller
{
    /**
     * Liste des présences
     */
    public function index(Request $request)
    {
        $query = Attendance::with([
            'student',
            'subject',
            'teacher'
        ]);

        // Recherche élève
        if ($request->filled('search')) {

            $search = trim($request->input('search'));

            $query->whereHas('student', function ($q) use ($search) {

                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('parent_name', 'like', "%{$search}%");
            });
        }

        // Filtre date
        if ($request->filled('date')) {
            $query->whereDate(
                'date',
                $request->input('date')
            );
        }

        // Filtre matière
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
     * Formulaire pour enregistrer une séance
     */
    public function create()
    {
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
            'attendances.create',
            compact(
                'students',
                'subjects',
                'teachers'
            )
        );
    }


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
            'nullable',
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
            'exists:students,id',
        ],

        'students.*.status' => [
            'required',
            'in:present,absent,late,justified',
        ],

        'students.*.note' => [
            'nullable',
            'string',
        ],
    ]);

    DB::transaction(function () use ($validated) {

        foreach ($validated['students'] as $studentData) {

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
$this->createPaymentSignalement(
    $student['id'],
    $validated['subject_id'],
    $validated['date']
);
            /*
            |--------------------------------------------------------------------------
            | Vérification du paiement
            |--------------------------------------------------------------------------
            |
            | فقط إذا كان الطالب حاضر أو متأخر.
            |
            */

            if (in_array($studentData['status'], ['present', 'late'])) {

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
            'Les présences ont été enregistrées avec succès.'
        );
}

    /**
     * Afficher les détails d'une séance
     */
    public function show(Attendance $attendance)
    {
        $attendance->load([
            'student',
            'subject',
            'teacher'
        ]);

        $attendances = Attendance::with([
            'student',
            'subject',
            'teacher'
        ])
            ->where('date', $attendance->date)
            ->where('subject_id', $attendance->subject_id)
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
     * Formulaire de modification d'une séance
     */
    public function edit(Attendance $attendance)
    {
        // Charger la présence sélectionnée
        $attendance->load([
            'student',
            'subject',
            'teacher'
        ]);

        // Charger toutes les présences de la même séance
        $attendances = Attendance::with([
            'student',
            'subject',
            'teacher'
        ])
            ->where('date', $attendance->date)
            ->where('subject_id', $attendance->subject_id)
            ->orderBy('id')
            ->get();

        // Liste des élèves
        $students = Student::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Liste des matières
        $subjects = Subject::query()
            ->orderBy('name')
            ->get();

        // Liste des enseignants
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
     * Mise à jour des présences
     */
    public function update(Request $request, Attendance $attendance)
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
                'nullable',
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
                'exists:students,id',
            ],

            'students.*.status' => [
                'required',
                'in:present,absent,late,justified',
            ],

            'students.*.note' => [
                'nullable',
                'string',
            ],
        ]);


        DB::transaction(function () use ($validated, $attendance) {

            // Séance originale
            $oldDate = $attendance->date;
            $oldSubjectId = $attendance->subject_id;

            // Mise à jour / création des présences
            foreach ($validated['students'] as $student) {

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
            }

            // Supprimer les anciennes présences
            // qui ne font plus partie de la séance
            Attendance::where('date', $oldDate)
                ->where('subject_id', $oldSubjectId)
                ->whereNotIn(
                    'student_id',
                    collect($validated['students'])
                        ->pluck('id')
                        ->toArray()
                )
                ->delete();
        });


        return redirect()
            ->route('attendances.index')
            ->with(
                'success',
                'Les présences ont été modifiées avec succès.'
            );
    }


    /**
     * Supprimer une présence
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()
            ->route('attendances.index')
            ->with(
                'success',
                'La présence a été supprimée avec succès.'
            );
    }


    /**
     * Impression d'une séance de présence
     */
    public function print(Attendance $attendance)
    {
        $attendance->load([
            'student',
            'subject',
            'teacher'
        ]);

        $attendances = Attendance::with([
            'student',
            'subject',
            'teacher'
        ])
            ->where('date', $attendance->date)
            ->where('subject_id', $attendance->subject_id)
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
    /**
 * Vérifier le paiement lié à une présence
 */
private function checkPaymentForAttendance(Attendance $attendance)
{
    $date = $attendance->date;

    /*
    |--------------------------------------------------------------------------
    | 1. Chercher le dernier paiement actif
    |    pour cet élève + cette matière
    |--------------------------------------------------------------------------
    */

    $payment = Payment::where(
            'student_id',
            $attendance->student_id
        )
        ->where(
            'subject_id',
            $attendance->subject_id
        )
        ->latest('payment_date')
        ->latest('id')
        ->first();


    /*
    |--------------------------------------------------------------------------
    | Aucun paiement trouvé
    |--------------------------------------------------------------------------
    */

    if (!$payment) {

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | 2. ABONNEMENT MENSUEL
    |--------------------------------------------------------------------------
    */

    if ($payment->payment_type === 'monthly') {

        /*
        | Le paiement doit concerner le mois de la présence
        */

        $month = $date->format('m');
        $year  = $date->format('Y');

        $monthlyPayment = Payment::where(
                'student_id',
                $attendance->student_id
            )
            ->where(
                'subject_id',
                $attendance->subject_id
            )
            ->where(
                'payment_type',
                'monthly'
            )
            ->whereYear(
                'payment_date',
                $year
            )
            ->whereMonth(
                'payment_date',
                $month
            )
            ->where(
                'remaining_amount',
                '<=',
                0
            )
            ->first();


        /*
        | إذا الشهر مدفوع بالكامل
        */

        if ($monthlyPayment) {

            return;
        }


        /*
        | هل يوجد signalement لهذا الشهر؟
        */

        $exists = PaymentSignalement::where(
                'student_id',
                $attendance->student_id
            )
            ->where(
                'subject_id',
                $attendance->subject_id
            )
            ->where(
                'period',
                $date->format('Y-m')
            )
            ->whereNull('attendance_date')
            ->whereIn(
                'status',
                ['pending', 'sent']
            )
            ->exists();


        if (!$exists) {

            PaymentSignalement::create([

                'student_id' =>
                    $attendance->student_id,

                'subject_id' =>
                    $attendance->subject_id,

                'payment_id' =>
                    $payment->id,

                'period' =>
                    $date->format('Y-m'),

                'amount_remaining' =>
                    $payment->remaining_amount,

                'status' =>
                    'pending',

                'signalement_date' =>
                    now()->toDateString(),

                'attendance_date' =>
                    null,

                'sent_at' =>
                    null,

                'note' =>
                    'Abonnement mensuel non réglé pour ce mois.',
            ]);
        }

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | 3. ABONNEMENT VIP
    |--------------------------------------------------------------------------
    */

    if ($payment->payment_type === 'vip') {

        /*
        | البحث عن paiement VIP لهذا اليوم بالضبط
        */

        $vipPayment = Payment::where(
                'student_id',
                $attendance->student_id
            )
            ->where(
                'subject_id',
                $attendance->subject_id
            )
            ->where(
                'payment_type',
                'vip'
            )
            ->whereDate(
                'payment_date',
                $date
            )
            ->where(
                'remaining_amount',
                '<=',
                0
            )
            ->first();


        /*
        | إذا دفع VIP لهذا اليوم
        */

        if ($vipPayment) {

            return;
        }


        /*
        | هل signalement لهذا اليوم موجود؟
        */

        $exists = PaymentSignalement::where(
                'student_id',
                $attendance->student_id
            )
            ->where(
                'subject_id',
                $attendance->subject_id
            )
            ->whereDate(
                'attendance_date',
                $date
            )
            ->whereIn(
                'status',
                ['pending', 'sent']
            )
            ->exists();


        /*
        | إنشاء signalement جديد
        */

        if (!$exists) {

            PaymentSignalement::create([

                'student_id' =>
                    $attendance->student_id,

                'subject_id' =>
                    $attendance->subject_id,

                'payment_id' =>
                    $payment->id,

                'period' =>
                    $date->format('Y-m-d'),

                'amount_remaining' =>
                    $payment->remaining_amount,

                'status' =>
                    'pending',

                'signalement_date' =>
                    now()->toDateString(),

                'attendance_date' =>
                    $date,

                'sent_at' =>
                    null,

                'note' =>
                    'Paiement VIP du jour non effectué.',
            ]);
        }
    }
}
/**
 * Créer un signalement si le paiement de la matière
 * présente un reste à payer.
 */
private function createPaymentSignalement(
    int $studentId,
    int $subjectId,
    string $date
): void {
    $periodMap = [
        '01' => 'Janvier',
        '02' => 'Février',
        '03' => 'Mars',
        '04' => 'Avril',
        '05' => 'Mai',
        '06' => 'Juin',
        '07' => 'Juillet',
        '08' => 'Août',
        '09' => 'Septembre',
        '10' => 'Octobre',
        '11' => 'Novembre',
        '12' => 'Décembre',
    ];

    $month = date('m', strtotime($date));

    $period = $periodMap[$month] ?? null;

    if (!$period) {
        return;
    }

    $payment = \App\Models\Payment::where(
        'student_id',
        $studentId
    )
        ->where(
            'subject_id',
            $subjectId
        )
        ->where(
            'period',
            $period
        )
        ->where(
            'remaining_amount',
            '>',
            0
        )
        ->latest('id')
        ->first();

    // Aucun paiement ou paiement entièrement réglé
    if (!$payment) {
        return;
    }

    // Éviter les doublons
    $exists = \App\Models\PaymentSignalement::where(
        'payment_id',
        $payment->id
    )
        ->where(
            'attendance_date',
            $date
        )
        ->exists();

    if ($exists) {
        return;
    }

    \App\Models\PaymentSignalement::create([
        'student_id' => $studentId,
        'subject_id' => $subjectId,
        'payment_id' => $payment->id,
        'period' => $payment->period,
        'amount_remaining' => $payment->remaining_amount,
        'status' => 'pending',
        'signalement_date' => now()->toDateString(),
        'attendance_date' => $date,
        'sent_at' => null,
        'note' => 'Impayé détecté lors de l\'enregistrement de la présence.',
    ]);
}
}