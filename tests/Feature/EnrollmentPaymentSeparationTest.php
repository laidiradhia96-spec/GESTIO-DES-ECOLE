<?php

use App\Models\Enrollment;
use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\Level;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

function sepSubject(): Subject
{
    return Subject::factory()->create([
        'active' => true,
        'primaire' => true,
        'moyen' => false,
        'lycee' => false,
    ]);
}

function sepTeacher(string $cycleCode = 'PRI'): Teacher
{
    $teacher = Teacher::factory()->create([
        'first_name' => 'Ahmed',
        'last_name' => 'Benali',
        'active' => true,
    ]);

    $teacher->levels()->attach(Level::firstOrCreate(
        ['code' => $cycleCode],
        ['name' => $cycleCode, 'active' => true]
    )->id);

    return $teacher;
}

function sepGroup(Subject $subject, Teacher $teacher, string $level, string $mode = 'normal', string $billingType = 'monthly', float $price = 1500): Group
{
    $year = SchoolYear::firstOrCreate(
        ['name' => '2025-2026'],
        ['start_date' => '2025-09-01', 'end_date' => '2026-08-31']
    );

    $group = Group::create([
        'name' => 'Groupe Séparation',
        'level' => $level,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => $mode,
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => $billingType,
        'student_price' => $price,
        'teacher_share' => $price * 0.6,
        'academy_share' => $price * 0.4,
        'effective_from' => now()->toDateString(),
    ]);

    return $group;
}

// =========================================================
// TEST 1: Enrollment succeeds without payment
// =========================================================

test('enrollment is created without requiring payment_type', function () {
    $user = User::factory()->create();
    $subject = sepSubject();
    $teacher = sepTeacher();
    $subject->teachers()->attach($teacher);
    $group = sepGroup($subject, $teacher, '1AP');

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'group_id' => $group->id,
        ]],
    ])->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $student = Student::where('first_name', 'Yasmine')->first();

    expect($student)->not->toBeNull()
        ->and($student->enrollments)->toHaveCount(1)
        ->and($student->enrollments->first()->payment_type)->toBeNull()
        ->and(Payment::where('student_id', $student->id)->exists())->toBeFalse();
});

// =========================================================
// TEST 2: Payment amount comes from GroupTariff
// =========================================================

test('payment amount_due comes from GroupTariff, not enrollment', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = sepGroup($subject, $teacher, '1AS', 'normal', 'monthly', 2500);

    Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'status' => 'active',
        'start_date' => now()->toDateString(),
    ]);

    $this->actingAs($user)->post(route('payments.store'), [
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'payment_type' => 'monthly',
        'period' => 'Septembre',
        'amount_paid' => 2500,
        'payment_method' => 'Espèces',
    ])->assertRedirect();

    $payment = Payment::where('student_id', $student->id)->first();

    expect($payment)->not->toBeNull()
        ->and((float) $payment->amount_due)->toBe(2500.0);
});

// =========================================================
// TEST 3: Changing GroupTariff doesn't affect old payments
// =========================================================

test('changing GroupTariff does not affect old payments', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = sepGroup($subject, $teacher, '1AS', 'normal', 'monthly', 1500);

    Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'status' => 'active',
        'start_date' => now()->toDateString(),
    ]);

    // Create payment at 1500
    $this->actingAs($user)->post(route('payments.store'), [
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'payment_type' => 'monthly',
        'period' => 'Septembre',
        'amount_paid' => 1500,
        'payment_method' => 'Espèces',
    ]);

    $payment = Payment::where('student_id', $student->id)->first();
    $oldAmountDue = (float) $payment->amount_due;

    // Change tariff to 2000
    $tariff = $group->tariffs()->first();
    $tariff->update(['student_price' => 2000]);

    // Old payment should still be 1500
    $payment->refresh();

    expect((float) $payment->amount_due)->toBe($oldAmountDue)
        ->and((float) $payment->amount_due)->toBe(1500.0);
});

// =========================================================
// TEST 4: normal + monthly → 1500/month
// =========================================================

test('normal + monthly: payment amount = 1500', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = sepGroup($subject, $teacher, '1AS', 'normal', 'monthly', 1500);

    Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'status' => 'active',
        'start_date' => now()->toDateString(),
    ]);

    $this->actingAs($user)->post(route('payments.store'), [
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'payment_type' => 'monthly',
        'period' => 'Septembre',
        'amount_paid' => 1500,
        'payment_method' => 'Espèces',
    ]);

    $payment = Payment::where('student_id', $student->id)->first();

    expect((float) $payment->amount_due)->toBe(1500.0);
});

// =========================================================
// TEST 5: vip + monthly → 6000/month
// =========================================================

test('vip + monthly: payment amount = 6000', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = sepGroup($subject, $teacher, '1AS', 'vip', 'monthly', 6000);

    Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'status' => 'active',
        'start_date' => now()->toDateString(),
    ]);

    $this->actingAs($user)->post(route('payments.store'), [
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'payment_type' => 'vip_monthly',
        'period' => 'Septembre',
        'amount_paid' => 6000,
        'payment_method' => 'Espèces',
    ]);

    $payment = Payment::where('student_id', $student->id)->first();

    expect((float) $payment->amount_due)->toBe(6000.0);
});

// =========================================================
// TEST 6: vip + per_session → 2000/session
// =========================================================

test('vip + per_session: payment amount = 2000', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = sepGroup($subject, $teacher, '1AS', 'vip', 'per_session', 2000);

    Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'status' => 'active',
        'start_date' => now()->toDateString(),
    ]);

    $this->actingAs($user)->post(route('payments.store'), [
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'payment_type' => 'vip_per_session',
        'period' => '2026-09-13',
        'amount_paid' => 2000,
        'payment_method' => 'Espèces',
    ]);

    $payment = Payment::where('student_id', $student->id)->first();

    expect((float) $payment->amount_due)->toBe(2000.0);
});

// =========================================================
// TEST 7: enrollment without payment + student_group created
// =========================================================

test('student creation creates enrollment and student_group without payment', function () {
    $user = User::factory()->create();
    $subject = sepSubject();
    $teacher = sepTeacher();
    $subject->teachers()->attach($teacher);
    $group = sepGroup($subject, $teacher, '1AP');

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'group_id' => $group->id,
        ]],
    ])->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $student = Student::where('first_name', 'Yasmine')->first();

    expect($student)->not->toBeNull()
        ->and($student->enrollments)->toHaveCount(1)
        ->and($student->groups()->where('groups.id', $group->id)->exists())->toBeTrue()
        ->and(Payment::where('student_id', $student->id)->exists())->toBeFalse();
});

// =========================================================
// TEST 8: multiple subjects with different groups
// =========================================================

test('multiple subjects with different group modes succeed without payment', function () {
    $user = User::factory()->create();
    $subject1 = sepSubject();
    $subject2 = sepSubject();
    $teacher = sepTeacher();
    $subject1->teachers()->attach($teacher);
    $subject2->teachers()->attach($teacher);
    $groupNormal = sepGroup($subject1, $teacher, '1AP', 'normal', 'monthly', 1500);
    $groupVip = sepGroup($subject2, $teacher, '1AP', 'vip', 'monthly', 6000);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [
            [
                'subject_id' => $subject1->id,
                'group_id' => $groupNormal->id,
            ],
            [
                'subject_id' => $subject2->id,
                'group_id' => $groupVip->id,
            ],
        ],
    ])->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $student = Student::where('first_name', 'Yasmine')->first();

    expect($student->enrollments)->toHaveCount(2)
        ->and(Payment::where('student_id', $student->id)->exists())->toBeFalse();
});

// =========================================================
// TEST 9: enrollment does not depend on payment_type
// =========================================================

test('enrollment does not require payment_type field', function () {
    $user = User::factory()->create();
    $subject = sepSubject();
    $teacher = sepTeacher();
    $subject->teachers()->attach($teacher);
    $group = sepGroup($subject, $teacher, '1AP');

    // Send without payment_type
    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'group_id' => $group->id,
        ]],
    ])->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $student = Student::where('first_name', 'Yasmine')->first();
    $enrollment = $student->enrollments->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->payment_type)->toBeNull()
        ->and($enrollment->subject_id)->toBe($subject->id)
        ->and($enrollment->teacher_id)->toBe($teacher->id);
});
