<?php

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentSignalement;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\PaymentSignalementService;

function monthlyEnrollment(Student $student, Subject $subject, Teacher $teacher): Enrollment
{
    return Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'start_date' => now()->toDateString(),
        'status' => 'active',
        'payment_type' => 'monthly',
    ]);
}

function vipEnrollment(Student $student, Subject $subject, Teacher $teacher): Enrollment
{
    return Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'start_date' => now()->toDateString(),
        'status' => 'active',
        'payment_type' => 'vip',
    ]);
}

function syncAttendance(PaymentSignalementService $service, Student $student, Subject $subject, string $date): void
{
    $service->syncFromAttendance(Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'date' => $date,
        'status' => 'present',
    ]));
}

test('monthly: one unpaid signalement per student + subject + month when no payment exists', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);
    monthlyEnrollment($student, $subject, $teacher);

    $service = app(PaymentSignalementService::class);

    syncAttendance($service, $student, $subject, '2026-08-05');
    syncAttendance($service, $student, $subject, '2026-08-12');
    syncAttendance($service, $student, $subject, '2026-08-20');

    $signalements = PaymentSignalement::where('student_id', $student->id)
        ->where('subject_id', $subject->id)
        ->where('period', '2026-08')
        ->get();

    expect($signalements)->toHaveCount(1)
        ->and($signalements->first()->status)->toBe('pending');
});

test('monthly: a payment covering the month resolves the open signalement', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);
    monthlyEnrollment($student, $subject, $teacher);

    $service = app(PaymentSignalementService::class);

    syncAttendance($service, $student, $subject, '2026-08-05');

    expect(PaymentSignalement::where('period', '2026-08')->where('status', 'pending')->count())->toBe(1);

    $payment = Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'payment_type' => 'monthly',
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => '2026-08-20',
    ]);

    $service->syncFromPayment($payment);

    $signalement = PaymentSignalement::where('period', '2026-08')->first();

    expect($signalement->status)->toBe('resolved')
        ->and((float) $signalement->amount_remaining)->toBe(0.0);
});

test('monthly: partial payment keeps a signalement with the remaining amount', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);
    monthlyEnrollment($student, $subject, $teacher);

    $service = app(PaymentSignalementService::class);

    Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'payment_type' => 'monthly',
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1000,
        'remaining_amount' => 500,
        'payment_date' => '2026-08-14',
    ]);

    syncAttendance($service, $student, $subject, '2026-08-20');

    $signalement = PaymentSignalement::where('period', '2026-08')->first();

    expect($signalement)->not->toBeNull()
        ->and((float) $signalement->amount_remaining)->toBe(500.0);
});

test('monthly: the new month creates a new obligation and the august payment does not pay september', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);
    monthlyEnrollment($student, $subject, $teacher);

    $service = app(PaymentSignalementService::class);

    Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'payment_type' => 'monthly',
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => '2026-08-20',
    ]);

    syncAttendance($service, $student, $subject, '2026-08-27');

    expect(PaymentSignalement::where('period', '2026-08')->where('status', 'pending')->count())->toBe(0);

    syncAttendance($service, $student, $subject, '2026-09-03');
    syncAttendance($service, $student, $subject, '2026-09-10');

    $september = PaymentSignalement::where('student_id', $student->id)
        ->where('subject_id', $subject->id)
        ->where('period', '2026-09')
        ->get();

    expect($september)->toHaveCount(1)
        ->and($september->first()->status)->toBe('pending');
});

test('vip: each day is an independent obligation and old debts are preserved', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);
    vipEnrollment($student, $subject, $teacher);

    $service = app(PaymentSignalementService::class);

    syncAttendance($service, $student, $subject, '2026-08-05');
    syncAttendance($service, $student, $subject, '2026-08-06');

    expect(PaymentSignalement::where('period', '2026-08-05')->where('status', 'pending')->count())->toBe(1)
        ->and(PaymentSignalement::where('period', '2026-08-06')->where('status', 'pending')->count())->toBe(1);

    // Paiement du 07/08 uniquement
    $payment = Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'payment_type' => 'vip',
        'period' => '2026-08-07',
        'amount_due' => 500,
        'amount_paid' => 500,
        'remaining_amount' => 0,
        'payment_date' => '2026-08-07',
    ]);

    $service->syncFromPayment($payment);

    syncAttendance($service, $student, $subject, '2026-08-07');

    // Journée payée → aucun signalement (🟢)
    expect(PaymentSignalement::where('period', '2026-08-07')->count())->toBe(0);

    // Les dettes du 05/08 et 06/08 restent impayées
    expect(PaymentSignalement::where('period', '2026-08-05')->where('status', 'pending')->count())->toBe(1)
        ->and(PaymentSignalement::where('period', '2026-08-06')->where('status', 'pending')->count())->toBe(1);

    // Nouvelle journée non payée → nouveau signalement
    syncAttendance($service, $student, $subject, '2026-08-08');

    expect(PaymentSignalement::where('period', '2026-08-08')->where('status', 'pending')->count())->toBe(1);
});

test('vip: a partially paid day keeps a signalement with the remaining amount', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);
    vipEnrollment($student, $subject, $teacher);

    $service = app(PaymentSignalementService::class);

    Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'payment_type' => 'vip',
        'period' => '2026-08-07',
        'amount_due' => 500,
        'amount_paid' => 200,
        'remaining_amount' => 300,
        'payment_date' => '2026-08-07',
    ]);

    syncAttendance($service, $student, $subject, '2026-08-07');

    $signalement = PaymentSignalement::where('period', '2026-08-07')->first();

    expect($signalement)->not->toBeNull()
        ->and($signalement->status)->toBe('pending')
        ->and((float) $signalement->amount_remaining)->toBe(300.0);
});

test('monthly: an october payment never settles an august debt, even with an august payment_date', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);
    monthlyEnrollment($student, $subject, $teacher);

    $service = app(PaymentSignalementService::class);

    syncAttendance($service, $student, $subject, '2026-08-05');

    expect(PaymentSignalement::where('period', '2026-08')->where('status', 'pending')->count())->toBe(1);

    $payment = Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'payment_type' => 'monthly',
        'period' => '2026-10',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => '2026-08-14',
    ]);

    $service->syncFromPayment($payment);

    expect(PaymentSignalement::where('period', '2026-08')->where('status', 'pending')->count())->toBe(1)
        ->and(PaymentSignalement::where('period', '2026-08')->where('status', 'resolved')->count())->toBe(0);
});

test('reconcile supprime le signalement mensuel quand la présence supprimée était la seule', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);
    monthlyEnrollment($student, $subject, $teacher);

    $service = app(PaymentSignalementService::class);

    $attendance = Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'date' => '2026-08-05',
        'status' => 'present',
    ]);

    $service->syncFromAttendance($attendance);

    expect(PaymentSignalement::where('period', '2026-08')->where('status', 'pending')->count())->toBe(1);

    $attendance->delete();
    $service->reconcileAfterAttendanceRemoval($student->id, $subject->id, '2026-08-05');

    expect(PaymentSignalement::where('period', '2026-08')->where('status', 'pending')->count())->toBe(0);
});

test('reconcile conserve le signalement mensuel si une autre présence justifie le mois', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);
    monthlyEnrollment($student, $subject, $teacher);

    $service = app(PaymentSignalementService::class);

    $attendance = Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'date' => '2026-08-05',
        'status' => 'present',
    ]);

    $service->syncFromAttendance($attendance);

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'date' => '2026-08-19',
        'status' => 'justified',
    ]);

    $attendance->delete();
    $service->reconcileAfterAttendanceRemoval($student->id, $subject->id, '2026-08-05');

    expect(PaymentSignalement::where('period', '2026-08')->where('status', 'pending')->count())->toBe(1);
});

test('reconcile conserve le signalement résolu lors de la suppression d\'une présence', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);
    monthlyEnrollment($student, $subject, $teacher);

    $service = app(PaymentSignalementService::class);

    $attendance = Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'date' => '2026-08-05',
        'status' => 'present',
    ]);

    $service->syncFromAttendance($attendance);

    PaymentSignalement::where('period', '2026-08')->update([
        'status' => 'resolved',
        'amount_remaining' => 0,
    ]);

    $attendance->delete();
    $service->reconcileAfterAttendanceRemoval($student->id, $subject->id, '2026-08-05');

    expect(PaymentSignalement::where('period', '2026-08')->where('status', 'resolved')->count())->toBe(1);
});

test('reconcile supprime le signalement VIP du jour quand la présence supprimée était la seule', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);
    vipEnrollment($student, $subject, $teacher);

    $service = app(PaymentSignalementService::class);

    $attendance = Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'date' => '2026-08-05',
        'status' => 'present',
    ]);

    $service->syncFromAttendance($attendance);

    expect(PaymentSignalement::where('period', '2026-08-05')->where('status', 'pending')->count())->toBe(1);

    $attendance->delete();
    $service->reconcileAfterAttendanceRemoval($student->id, $subject->id, '2026-08-05');

    expect(PaymentSignalement::where('period', '2026-08-05')->where('status', 'pending')->count())->toBe(0);
});
