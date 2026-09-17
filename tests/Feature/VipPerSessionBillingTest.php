<?php

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\UnpaidDebtService;

// =========================================================
// HELPERS
// =========================================================

function vipGroup(Subject $subject, Teacher $teacher, float $price = 2000, string $name = 'VIP Group'): Group
{
    $year = SchoolYear::firstOrCreate(
        ['name' => '2025-2026'],
        ['start_date' => '2025-09-01', 'end_date' => '2026-08-31', 'is_current' => true]
    );

    $group = Group::create([
        'name' => $name,
        'level' => '1AP',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'vip',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'per_session',
        'student_price' => $price,
        'teacher_share' => $price * 0.6,
        'academy_share' => $price * 0.4,
        'effective_from' => '2025-09-01',
        'is_active' => true,
    ]);

    return $group;
}

function vipGroupEnroll(Student $student, Group $group, Subject $subject): Enrollment
{
    $teacher = $group->teacher;

    $group->students()->attach($student->id, ['joined_at' => '2026-08-01', 'is_active' => true]);

    return Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'start_date' => '2026-08-01',
        'status' => 'active',
        'payment_type' => null,
    ]);
}

function vipAttendance(Student $student, Subject $subject, Group $group, string $date): Attendance
{
    return Attendance::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $group->teacher_id,
        'group_id' => $group->id,
        'date' => $date,
        'status' => 'present',
        'school_year_id' => SchoolYear::forDate($date)?->id,
    ]);
}

function vipPayment(Student $student, Subject $subject, Group $group, string $period, float $amount): Payment
{
    return Payment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'school_year_id' => $group->school_year_id,
        'receipt_number' => 'REC-TEST-'.uniqid(),
        'payment_type' => 'vip_per_session',
        'period' => $period,
        'amount_due' => $amount,
        'amount_paid' => $amount,
        'remaining_amount' => 0,
        'teacher_share' => $amount * 0.6,
        'academy_share' => $amount * 0.4,
        'payment_method' => 'Espèces',
        'payment_date' => $period,
        'payment_time' => now()->format('H:i:s'),
    ]);
}

function monthlyGroup(Subject $subject, Teacher $teacher, float $price = 1500, string $name = 'Monthly Group'): Group
{
    $year = SchoolYear::firstOrCreate(
        ['name' => '2025-2026'],
        ['start_date' => '2025-09-01', 'end_date' => '2026-08-31', 'is_current' => true]
    );

    $group = Group::create([
        'name' => $name,
        'level' => '1AP',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => $price,
        'teacher_share' => $price * 0.6,
        'academy_share' => $price * 0.4,
        'effective_from' => '2025-09-01',
        'is_active' => true,
    ]);

    return $group;
}

function monthlyGroupEnroll(Student $student, Group $group, Subject $subject): Enrollment
{
    $teacher = $group->teacher;

    $group->students()->attach($student->id, ['joined_at' => '2026-08-01', 'is_active' => true]);

    return Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'start_date' => '2026-08-01',
        'status' => 'active',
        'payment_type' => null,
    ]);
}

// =========================================================
// TEST 1: Aucune présence → dette = 0
// =========================================================

test('vip per_session: zero attendance produces zero debt', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);

    $group = vipGroup($subject, $teacher, 2000);
    vipGroupEnroll($student, $group, $subject);

    $debts = app(UnpaidDebtService::class)->activeDebts();

    $studentDebts = $debts->filter(fn ($d) => $d->student->id === $student->id);
    expect($studentDebts)->toHaveCount(0);
});

// =========================================================
// TEST 2: Une présence non payée → dette = 2000
// =========================================================

test('vip per_session: one unpaid attendance produces debt of student_price', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);

    $group = vipGroup($subject, $teacher, 2000);
    vipGroupEnroll($student, $group, $subject);
    vipAttendance($student, $subject, $group, '2026-09-01');

    $debts = app(UnpaidDebtService::class)->activeDebts();

    $studentDebt = $debts->first(fn ($d) => $d->student->id === $student->id);
    expect($studentDebt)->not->toBeNull()
        ->and($studentDebt->amount_due)->toBe(2000.0)
        ->and($studentDebt->amount_remaining)->toBe(2000.0)
        ->and($studentDebt->period)->toBe('2026-09-01');
});

// =========================================================
// TEST 3: Plusieurs présences non payées → dette = 8000
// =========================================================

test('vip per_session: four unpaid attendances produce debt of 4x student_price', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);

    $group = vipGroup($subject, $teacher, 2000);
    vipGroupEnroll($student, $group, $subject);

    vipAttendance($student, $subject, $group, '2026-09-01');
    vipAttendance($student, $subject, $group, '2026-09-03');
    vipAttendance($student, $subject, $group, '2026-09-08');
    vipAttendance($student, $subject, $group, '2026-09-10');

    $debts = app(UnpaidDebtService::class)->activeDebts();

    $studentDebts = $debts->filter(fn ($d) => $d->student->id === $student->id);
    expect($studentDebts)->toHaveCount(4);

    $totalRemaining = $studentDebts->sum('amount_remaining');
    expect($totalRemaining)->toBe(8000.0);
});

// =========================================================
// TEST 4: Une séance payée → dette = 6000
// =========================================================

test('vip per_session: one paid session out of four reduces debt by student_price', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);

    $group = vipGroup($subject, $teacher, 2000);
    vipGroupEnroll($student, $group, $subject);

    vipAttendance($student, $subject, $group, '2026-09-01');
    vipAttendance($student, $subject, $group, '2026-09-03');
    vipAttendance($student, $subject, $group, '2026-09-08');
    vipAttendance($student, $subject, $group, '2026-09-10');

    // Pay for Sept 8
    vipPayment($student, $subject, $group, '2026-09-08', 2000);

    $debts = app(UnpaidDebtService::class)->activeDebts();

    $studentDebts = $debts->filter(fn ($d) => $d->student->id === $student->id);
    expect($studentDebts)->toHaveCount(3);

    $totalRemaining = $studentDebts->sum('amount_remaining');
    expect($totalRemaining)->toBe(6000.0);
});

// =========================================================
// TEST 5: Deux séances payées → dette = 4000
// =========================================================

test('vip per_session: two paid sessions out of four reduces debt to 2x student_price', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);

    $group = vipGroup($subject, $teacher, 2000);
    vipGroupEnroll($student, $group, $subject);

    vipAttendance($student, $subject, $group, '2026-09-01');
    vipAttendance($student, $subject, $group, '2026-09-03');
    vipAttendance($student, $subject, $group, '2026-09-08');
    vipAttendance($student, $subject, $group, '2026-09-10');

    // Pay for Sept 1 and Sept 8
    vipPayment($student, $subject, $group, '2026-09-01', 2000);
    vipPayment($student, $subject, $group, '2026-09-08', 2000);

    $debts = app(UnpaidDebtService::class)->activeDebts();

    $studentDebts = $debts->filter(fn ($d) => $d->student->id === $student->id);
    expect($studentDebts)->toHaveCount(2);

    $totalRemaining = $studentDebts->sum('amount_remaining');
    expect($totalRemaining)->toBe(4000.0);
});

// =========================================================
// TEST 6: Toutes les séances payées → dette = 0
// =========================================================

test('vip per_session: all four sessions paid produces zero debt', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);

    $group = vipGroup($subject, $teacher, 2000);
    vipGroupEnroll($student, $group, $subject);

    vipAttendance($student, $subject, $group, '2026-09-01');
    vipAttendance($student, $subject, $group, '2026-09-03');
    vipAttendance($student, $subject, $group, '2026-09-08');
    vipAttendance($student, $subject, $group, '2026-09-10');

    vipPayment($student, $subject, $group, '2026-09-01', 2000);
    vipPayment($student, $subject, $group, '2026-09-03', 2000);
    vipPayment($student, $subject, $group, '2026-09-08', 2000);
    vipPayment($student, $subject, $group, '2026-09-10', 2000);

    $debts = app(UnpaidDebtService::class)->activeDebts();

    $studentDebts = $debts->filter(fn ($d) => $d->student->id === $student->id);
    expect($studentDebts)->toHaveCount(0);
});

// =========================================================
// TEST 7: Plusieurs groupes → dettes séparées
// =========================================================

test('vip per_session: debts are isolated per group', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher1 = Teacher::factory()->create();
    $teacher2 = Teacher::factory()->create();
    $subject->teachers()->attach([$teacher1->id, $teacher2->id]);

    // Math VIP Group at 2000/session (different teacher)
    $mathGroup = vipGroup($subject, $teacher1, 2000, 'Math VIP');
    vipGroupEnroll($student, $mathGroup, $subject);

    // Physics VIP Group at 2500/session (different teacher for unique constraint)
    $physicsGroup = vipGroup($subject, $teacher2, 2500, 'Physics VIP');
    vipGroupEnroll($student, $physicsGroup, $subject);

    // 2 attendances in Math group
    vipAttendance($student, $subject, $mathGroup, '2026-09-01');
    vipAttendance($student, $subject, $mathGroup, '2026-09-03');

    // 3 attendances in Physics group
    vipAttendance($student, $subject, $physicsGroup, '2026-09-01');
    vipAttendance($student, $subject, $physicsGroup, '2026-09-03');
    vipAttendance($student, $subject, $physicsGroup, '2026-09-08');

    $debts = app(UnpaidDebtService::class)->activeDebts();

    $studentDebts = $debts->filter(fn ($d) => $d->student->id === $student->id);

    // 2 from Math + 3 from Physics = 5 total
    expect($studentDebts)->toHaveCount(5);

    // Pay for one Math session
    vipPayment($student, $subject, $mathGroup, '2026-09-01', 2000);

    $debtsAfter = app(UnpaidDebtService::class)->activeDebts();
    $studentDebtsAfter = $debtsAfter->filter(fn ($d) => $d->student->id === $student->id);

    // Math: 1 remaining (2000) + Physics: 3 remaining (7500) = 4 debts, 9500 total
    $mathDebts = $studentDebtsAfter->filter(fn ($d) => $d->amount_due == 2000.0);
    $physicsDebts = $studentDebtsAfter->filter(fn ($d) => $d->amount_due == 2500.0);

    expect($mathDebts)->toHaveCount(1)
        ->and($physicsDebts)->toHaveCount(3);

    $totalRemaining = $studentDebtsAfter->sum('amount_remaining');
    expect($totalRemaining)->toBe(9500.0);
});

// =========================================================
// TEST 8: Monthly ne change pas — Normal 1500/month, 4 présences → dette = 1500
// =========================================================

test('monthly: four attendances still produce one monthly obligation of student_price', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);

    $group = monthlyGroup($subject, $teacher, 1500);
    monthlyGroupEnroll($student, $group, $subject);

    // 4 attendances in the same month
    Attendance::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'date' => '2026-09-01',
        'status' => 'present',
        'school_year_id' => SchoolYear::forDate('2026-09-01')?->id,
    ]);
    Attendance::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'date' => '2026-09-05',
        'status' => 'present',
        'school_year_id' => SchoolYear::forDate('2026-09-05')?->id,
    ]);
    Attendance::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'date' => '2026-09-10',
        'status' => 'present',
        'school_year_id' => SchoolYear::forDate('2026-09-10')?->id,
    ]);
    Attendance::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'date' => '2026-09-15',
        'status' => 'present',
        'school_year_id' => SchoolYear::forDate('2026-09-15')?->id,
    ]);

    $debts = app(UnpaidDebtService::class)->activeDebts();

    $studentDebts = $debts->filter(fn ($d) => $d->student->id === $student->id);
    expect($studentDebts)->toHaveCount(1)
        ->and($studentDebts->first()->amount_due)->toBe(1500.0)
        ->and($studentDebts->first()->amount_remaining)->toBe(1500.0)
        ->and($studentDebts->first()->period)->toBe('2026-09');
});

// =========================================================
// TEST 9: Spécial Monthly — 6000/month, 4 présences → dette = 6000
// =========================================================

test('special_monthly: four attendances still produce one monthly obligation of student_price', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);

    $year = SchoolYear::firstOrCreate(
        ['name' => '2025-2026'],
        ['start_date' => '2025-09-01', 'end_date' => '2026-08-31', 'is_current' => true]
    );

    $group = Group::create([
        'name' => 'Spécial Group',
        'level' => '1AP',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'special',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 6000,
        'teacher_share' => 3600,
        'academy_share' => 2400,
        'effective_from' => '2025-09-01',
        'is_active' => true,
    ]);

    $group->students()->attach($student->id, ['joined_at' => '2026-08-01', 'is_active' => true]);

    Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'start_date' => '2026-08-01',
        'status' => 'active',
        'payment_type' => null,
    ]);

    // 4 attendances
    foreach (['2026-09-01', '2026-09-05', '2026-09-10', '2026-09-15'] as $date) {
        Attendance::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'date' => $date,
            'status' => 'present',
            'school_year_id' => SchoolYear::forDate($date)?->id,
        ]);
    }

    $debts = app(UnpaidDebtService::class)->activeDebts();

    $studentDebts = $debts->filter(fn ($d) => $d->student->id === $student->id);
    expect($studentDebts)->toHaveCount(1)
        ->and($studentDebts->first()->amount_due)->toBe(6000.0)
        ->and($studentDebts->first()->amount_remaining)->toBe(6000.0);
});

// =========================================================
// TEST 10: Changement de tarif — anciens paiements inchangés
// =========================================================

test('vip per_session: tariff change does not affect historical payments', function () {
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);

    $year = SchoolYear::firstOrCreate(
        ['name' => '2025-2026'],
        ['start_date' => '2025-09-01', 'end_date' => '2026-08-31', 'is_current' => true]
    );

    $group = Group::create([
        'name' => 'VIP Group',
        'level' => '1AP',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'vip',
        'is_active' => true,
    ]);

    // Initial tariff: 2000/session
    $tariff = GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'per_session',
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
        'effective_from' => '2025-09-01',
        'is_active' => true,
    ]);

    $group->students()->attach($student->id, ['joined_at' => '2026-08-01', 'is_active' => true]);

    $enrollment = Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'start_date' => '2026-08-01',
        'status' => 'active',
        'payment_type' => null,
    ]);

    // Pay at old price
    $payment = Payment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'school_year_id' => $year->id,
        'receipt_number' => 'REC-TEST-OLD',
        'payment_type' => 'vip_per_session',
        'period' => '2026-09-01',
        'amount_due' => 2000,
        'amount_paid' => 2000,
        'remaining_amount' => 0,
        'teacher_share' => 1200,
        'academy_share' => 800,
        'payment_method' => 'Espèces',
        'payment_date' => '2026-09-01',
        'payment_time' => now()->format('H:i:s'),
    ]);

    // Change tariff to 2500/session
    $tariff->update(['is_active' => false]);
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'per_session',
        'student_price' => 2500,
        'teacher_share' => 1500,
        'academy_share' => 1000,
        'effective_from' => '2026-09-15',
        'is_active' => true,
    ]);

    // Old payment snapshots must remain unchanged
    $payment->refresh();
    expect((float) $payment->amount_due)->toBe(2000.0)
        ->and((float) $payment->teacher_share)->toBe(1200.0)
        ->and((float) $payment->academy_share)->toBe(800.0);

    // New attendance uses new price
    vipAttendance($student, $subject, $group, '2026-09-16');

    $debts = app(UnpaidDebtService::class)->activeDebts();
    $studentDebts = $debts->filter(fn ($d) => $d->student->id === $student->id);

    // Sept 1 is paid, Sept 16 is unpaid at new price
    expect($studentDebts)->toHaveCount(1)
        ->and($studentDebts->first()->amount_due)->toBe(2500.0)
        ->and($studentDebts->first()->period)->toBe('2026-09-16');
});
