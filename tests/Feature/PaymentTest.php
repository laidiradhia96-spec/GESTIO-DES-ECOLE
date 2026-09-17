<?php

use App\Models\Enrollment;
use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\Payment;
use App\Models\PaymentSignalement;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\PaymentSignalementService;
use App\Services\RevenueService;

// =========================================================
// HELPERS
// =========================================================

function createPaymentGroup(array $studentIds, int $teacherId, int $subjectId, string $mode = 'normal', string $billingType = 'monthly', float $price = 1500): Group
{
    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $group = Group::create([
        'name' => $mode === 'vip' ? 'Groupe VIP' : ($mode === 'special' ? 'Groupe Spécial' : 'Groupe Normal'),
        'level' => '1AS',
        'subject_id' => $subjectId,
        'teacher_id' => $teacherId,
        'school_year_id' => $year->id,
        'max_students' => 30,
        'mode' => $mode,
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => $billingType,
        'student_price' => $price,
        'teacher_share' => $price * 0.6,
        'academy_share' => $price * 0.4,
        'effective_from' => '2026-01-01',
    ]);

    foreach ($studentIds as $studentId) {
        $group->students()->attach($studentId, [
            'joined_at' => now(),
            'is_active' => 1,
        ]);

        Enrollment::create([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'teacher_id' => $teacherId,
            'group_id' => $group->id,
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'payment_type' => $billingType,
            'school_year_id' => $year->id,
        ]);
    }

    return $group;
}

function paymentPayload(Student $student, Subject $subject, Group $group, array $overrides = []): array
{
    return array_merge([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'payment_type' => 'monthly',
        'period' => 'Septembre',
        'amount_paid' => 1500,
        'payment_method' => 'Espèces',
    ], $overrides);
}

// =========================================================
// TEST 1: Normal group + monthly
// =========================================================

test('normal group + monthly: amount automatically = tariff student_price', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'normal', 'monthly', 1500);

    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group));

    $response->assertRedirect(route('payments.index'))
        ->assertSessionHas('success');

    $payment = Payment::first();

    expect($payment)->not->toBeNull()
        ->and($payment->amount_due)->toBe('1500.00')
        ->and($payment->group_id)->toBe($group->id)
        ->and($payment->payment_type)->toBe('monthly');
});

// =========================================================
// TEST 2: VIP monthly
// =========================================================

test('vip monthly: amount automatically = vip monthly tariff', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'vip', 'monthly', 6000);

    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group, [
        'payment_type' => 'vip_monthly',
    ]));

    $response->assertRedirect(route('payments.index'))
        ->assertSessionHas('success');

    $payment = Payment::first();

    expect($payment)->not->toBeNull()
        ->and($payment->amount_due)->toBe('6000.00')
        ->and($payment->payment_type)->toBe('vip_monthly');
});

// =========================================================
// TEST 3: VIP per session
// =========================================================

test('vip per session: amount automatically = per_session tariff', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'vip', 'per_session', 2000);

    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group, [
        'payment_type' => 'vip_per_session',
    ]));

    $response->assertRedirect(route('payments.index'))
        ->assertSessionHas('success');

    $payment = Payment::first();

    expect($payment)->not->toBeNull()
        ->and($payment->amount_due)->toBe('2000.00')
        ->and($payment->payment_type)->toBe('vip_per_session');
});

// =========================================================
// TEST 3b: Special group + monthly → special_monthly
// =========================================================

test('special_group_monthly payment works', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'special', 'monthly', 6000);

    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group, [
        'payment_type' => 'special_monthly',
    ]));

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect(Payment::count())->toBe(1);

    $payment = Payment::first();
    expect($payment)->not->toBeNull()
        ->and($payment->amount_due)->toBe('6000.00')
        ->and($payment->payment_type)->toBe('special_monthly');
});

// =========================================================
// TEST 3c: Reject vip_monthly on special group (mode mismatch)
// =========================================================

test('reject vip_monthly payment on special group', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'special', 'monthly', 6000);

    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group, [
        'payment_type' => 'vip_monthly',
    ]));

    $response->assertSessionHasErrors('payment_type');
    expect(Payment::count())->toBe(0);
});

// =========================================================
// TEST 4: Reject VIP payment on Normal group
// =========================================================

test('reject vip_monthly payment on normal group', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'normal', 'monthly', 1500);

    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group, [
        'payment_type' => 'vip_monthly',
    ]));

    $response->assertSessionHasErrors('payment_type');
    expect(Payment::count())->toBe(0);
});

// =========================================================
// TEST 5: Reject per_session on Normal group
// =========================================================

test('reject vip_per_session payment on normal group', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'normal', 'monthly', 1500);

    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group, [
        'payment_type' => 'vip_per_session',
    ]));

    $response->assertSessionHasErrors('payment_type');
    expect(Payment::count())->toBe(0);
});

// =========================================================
// TEST 6: Reject student not in group
// =========================================================

test('reject student not in group', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([], $teacher->id, $subject->id, 'normal', 'monthly', 1500);

    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group));

    $response->assertSessionHasErrors('student_id');
    expect(Payment::count())->toBe(0);
});

// =========================================================
// TEST 7: Reject group not for selected subject
// =========================================================

test('reject group not for selected subject', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject1 = Subject::factory()->create();
    $subject2 = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject1->id, 'normal', 'monthly', 1500);

    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject2, $group));

    $response->assertSessionHasErrors('group_id');
    expect(Payment::count())->toBe(0);
});

// =========================================================
// TEST 8: Reject inactive group
// =========================================================

test('reject inactive group', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'normal', 'monthly', 1500);
    $group->update(['is_active' => false]);

    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group));

    $response->assertSessionHasErrors('group_id');
    expect(Payment::count())->toBe(0);
});

// =========================================================
// TEST 9: Reject inactive tariff
// =========================================================

test('reject group with no active tariff', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'normal', 'monthly', 1500);
    $group->tariffs()->update(['is_active' => false]);

    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group));

    $response->assertSessionHasErrors('group_id');
    expect(Payment::count())->toBe(0);
});

// =========================================================
// TEST 10: Tariff change doesn't affect historical payments
// =========================================================

test('tariff change does not affect historical payment', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'normal', 'monthly', 1500);

    $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group));

    $payment = Payment::first();
    expect((float) $payment->amount_due)->toBe(1500.0);

    // Change tariff
    $group->tariffs()->update(['is_active' => false]);
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 1800,
        'teacher_share' => 1080,
        'academy_share' => 720,
        'effective_from' => now()->addDay()->toDateString(),
    ]);

    $payment->refresh();
    expect((float) $payment->amount_due)->toBe(1500.0);
});

// =========================================================
// TEST 11: Frontend tamper protection (amount_due from tariff)
// =========================================================

test('amount_due is computed from tariff, not from request', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'normal', 'monthly', 1500);

    $payload = paymentPayload($student, $subject, $group, [
        'amount_due' => 99999,
    ]);

    $this->actingAs($user)->post(route('payments.store'), $payload);

    $payment = Payment::first();
    expect((float) $payment->amount_due)->toBe(1500.0);
});

// =========================================================
// TEST 12: Monthly payment requires month
// =========================================================

test('monthly payment requires period (month)', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'normal', 'monthly', 1500);

    $payload = paymentPayload($student, $subject, $group, [
        'period' => '',
    ]);

    $this->actingAs($user)->post(route('payments.store'), $payload)
        ->assertSessionHasErrors('period');

    expect(Payment::count())->toBe(0);
});

// =========================================================
// TEST 13: Per-session doesn't require monthly month
// =========================================================

test('per_session does not require monthly period', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'vip', 'per_session', 2000);

    $payload = paymentPayload($student, $subject, $group, [
        'payment_type' => 'vip_per_session',
        'period' => now()->format('Y-m-d'),
    ]);

    $this->actingAs($user)->post(route('payments.store'), $payload)
        ->assertRedirect(route('payments.index'))
        ->assertSessionHas('success');

    expect(Payment::count())->toBe(1);
});

// =========================================================
// LEGACY TESTS (updated to use groups)
// =========================================================

test('store enregistre une date et une heure de paiement antidatées', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id);

    $defaultYearId = SchoolYear::defaultId();

    $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group, [
        'payment_date' => '2026-08-03',
        'payment_time' => '14:30',
    ]))->assertRedirect(route('payments.index'))
        ->assertSessionHas('success');

    $payment = Payment::first();

    expect($payment->payment_date->format('Y-m-d'))->toBe('2026-08-03')
        ->and($payment->payment_time)->toBe('14:30:00')
        ->and($payment->school_year_id)->toBe($defaultYearId);
});

test('store rejette une date de paiement future', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id);

    $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group, [
        'payment_date' => now()->addDay()->toDateString(),
    ]))->assertSessionHasErrors('payment_date');

    expect(Payment::count())->toBe(0);
});

test('unpaid ne liste que les paiements non soldés', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id);

    $paid = Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
    ]);

    $unpaid = Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'amount_due' => 1500,
        'amount_paid' => 500,
        'remaining_amount' => 1000,
    ]);

    $this->actingAs($user)->get(route('payments.unpaid'))
        ->assertOk()
        ->assertSee($unpaid->receipt_number)
        ->assertDontSee($paid->receipt_number);
});

test('print affiche le reçu du paiement', function () {
    $user = User::factory()->create();
    $payment = Payment::factory()->create();

    $this->actingAs($user)->get(route('payments.print', $payment))
        ->assertOk()
        ->assertSee($payment->receipt_number);
});

test('edit affiche le formulaire pré-rempli', function () {
    $user = User::factory()->create();

    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $student = Student::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id);

    $payment = Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'payment_type' => 'monthly',
        'period' => 'Septembre',
    ]);

    $this->actingAs($user)->get(route('payments.edit', $payment))
        ->assertOk()
        ->assertSee('value="1500.00"', false)
        ->assertSee($payment->receipt_number);
});

test('update recalcule le reste et réconcilie le signalement', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id);

    $payment = Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'payment_type' => 'monthly',
        'period' => 'Septembre',
        'amount_due' => 1500,
        'amount_paid' => 1000,
        'remaining_amount' => 500,
        'payment_date' => '2026-09-10',
    ]);

    $service = app(PaymentSignalementService::class);
    $service->syncFromPayment($payment);

    $signalement = PaymentSignalement::where('period', '2026-09')->first();

    expect($signalement)->not->toBeNull()
        ->and((float) $signalement->amount_remaining)->toBe(500.0);

    $this->actingAs($user)->put(route('payments.update', $payment), paymentPayload($student, $subject, $group, [
        'amount_paid' => 1500,
        'payment_date' => '2026-09-12',
    ]))->assertRedirect(route('payments.index'))
        ->assertSessionHas('success');

    $payment->refresh();

    expect((float) $payment->remaining_amount)->toBe(0.0)
        ->and($payment->payment_date->format('Y-m-d'))->toBe('2026-09-12');

    $signalement->refresh();

    expect($signalement->status)->toBe('resolved')
        ->and((float) $signalement->amount_remaining)->toBe(0.0);
});

test('update conserve l\'élève du paiement', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id);

    $payment = Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'payment_type' => 'monthly',
        'period' => 'Septembre',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => '2026-09-10',
    ]);

    $this->actingAs($user)->put(route('payments.update', $payment), paymentPayload($student, $subject, $group, [
        'amount_paid' => 1000,
    ]))->assertRedirect(route('payments.index'));

    expect($payment->refresh()->student_id)->toBe($student->id);
});

test('show affiche la matière et l\'enseignant de l\'inscription de l\'élève', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create(['name' => 'Mathématiques']);
    $teacher = Teacher::factory()->create([
        'first_name' => 'Karim',
        'last_name' => 'Amrani',
    ]);

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id);

    $payment = Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
    ]);

    $this->actingAs($user)->get(route('payments.show', $payment))
        ->assertOk()
        ->assertSee('Mathématiques')
        ->assertSee($group->name);
});

test('subjects-by-student returns subjects from student groups', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id);

    $response = $this->actingAs($user)->getJson(route('payments.subjects-by-student', [
        'student_id' => $student->id,
    ]));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['id' => $subject->id, 'name' => $subject->name]);
});

test('groups-by-student-subject returns groups for student+subject', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id);

    $response = $this->actingAs($user)->getJson(route('payments.groups-by-student-subject', [
        'student_id' => $student->id,
        'subject_id' => $subject->id,
    ]));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['id' => $group->id]);
});

test('tariff-by-group returns active tariff', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'vip', 'per_session', 2000);

    $response = $this->actingAs($user)->getJson(route('payments.tariff-by-group', [
        'group_id' => $group->id,
    ]));

    $response->assertOk()
        ->assertJsonFragment([
            'found' => true,
            'student_price' => '2000.00',
            'billing_type' => 'per_session',
            'group_mode' => 'vip',
        ]);
});

test('store requires group_id', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $this->actingAs($user)->post(route('payments.store'), [
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'payment_type' => 'monthly',
        'period' => 'Septembre',
        'amount_paid' => 1500,
        'payment_method' => 'Espèces',
    ])->assertSessionHasErrors('group_id');

    expect(Payment::count())->toBe(0);
});

test('reject amount_paid exceeding tariff amount', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'normal', 'monthly', 1500);

    $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group, [
        'amount_paid' => 2000,
    ]))->assertSessionHasErrors('amount_paid');

    expect(Payment::count())->toBe(0);
});

test('create page loads students', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['last_name' => 'TestCreate']);

    $this->actingAs($user)->get(route('payments.create'))
        ->assertOk()
        ->assertSee('TestCreate');
});

test('payment update uses existing student_id and preserves snapshot', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $group = createPaymentGroup([$student->id], $teacher->id, $subject->id, 'normal', 'monthly', 2000);

    $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group, [
        'amount_paid' => 1000,
    ]));

    $payment = Payment::first();
    $originalStudentId = $payment->student_id;
    $originalGroupId = $payment->group_id;

    $response = $this->actingAs($user)->put(route('payments.update', $payment), [
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'payment_type' => 'monthly',
        'period' => 'Octobre',
        'amount_paid' => 500,
        'payment_method' => 'Virement',
    ]);

    $response->assertRedirect(route('payments.index'))
        ->assertSessionHas('success');

    $payment->refresh();
    expect($payment->student_id)->toBe($originalStudentId)
        ->and($payment->group_id)->toBe($originalGroupId)
        ->and((float) $payment->teacher_share)->toBe(300.00)
        ->and((float) $payment->academy_share)->toBe(200.00);
});

// =========================================================
// TEST A: Future tariff must never be used for a past payment
// =========================================================

test('future tariff must never be used for a past payment', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $group = Group::create([
        'name' => 'Groupe Normal',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'max_students' => 30,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    // September tariff: 2000, teacher=1200, academy=800
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
        'effective_from' => '2026-09-01',
        'effective_to' => '2026-09-30',
        'is_active' => true,
    ]);

    // October tariff: 2500, teacher=1500, academy=1000
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 2500,
        'teacher_share' => 1500,
        'academy_share' => 1000,
        'effective_from' => '2026-10-01',
        'effective_to' => null,
        'is_active' => true,
    ]);

    Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'start_date' => '2026-09-01',
        'status' => 'active',
        'payment_type' => 'monthly',
        'school_year_id' => $year->id,
    ]);

    // Payment dated September 15
    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group, [
        'amount_paid' => 1000,
        'payment_date' => '2026-09-15',
    ]));

    $response->assertRedirect(route('payments.index'))
        ->assertSessionHas('success');

    $payment = Payment::first();

    // Must use September tariff: 1000 × (1200/2000) = 600 teacher, 1000 × (800/2000) = 400 academy
    expect((float) $payment->teacher_share)->toBe(600.0);
    expect((float) $payment->academy_share)->toBe(400.0);

    // The key assertion: amount_due must be 2000 (September price), not 2500 (October price)
    expect((float) $payment->amount_due)->toBe(2000.0);
});

// =========================================================
// TEST B: September payment remains September after October tariff exists
// =========================================================

test('september payment remains september after october tariff exists', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $group = Group::create([
        'name' => 'Groupe Normal',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'max_students' => 30,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    // September tariff: 2000, teacher=1500, academy=500
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1500,
        'academy_share' => 500,
        'effective_from' => '2026-09-01',
        'effective_to' => '2026-09-30',
        'is_active' => true,
    ]);

    // October tariff: 2500, teacher=2000, academy=500
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 2500,
        'teacher_share' => 2000,
        'academy_share' => 500,
        'effective_from' => '2026-10-01',
        'effective_to' => null,
        'is_active' => true,
    ]);

    Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'start_date' => '2026-09-01',
        'status' => 'active',
        'payment_type' => 'monthly',
        'school_year_id' => $year->id,
    ]);

    // Create September payment
    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group, [
        'amount_paid' => 1000,
        'payment_date' => '2026-09-15',
    ]));

    $response->assertRedirect(route('payments.index'));

    $payment = Payment::first();

    // September snapshot: 1000 × (1500/2000) = 750 teacher, 1000 × (500/2000) = 250 academy
    expect((float) $payment->teacher_share)->toBe(750.0);
    expect((float) $payment->academy_share)->toBe(250.0);
    expect((float) $payment->amount_due)->toBe(2000.0);

    // Update the payment (simulating an edit in October)
    $response = $this->actingAs($user)->put(route('payments.update', $payment), [
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'payment_type' => 'monthly',
        'period' => 'Septembre',
        'amount_paid' => 1000,
        'payment_method' => 'Espèces',
        'payment_date' => '2026-09-15',
    ]);

    $response->assertRedirect();

    $payment->refresh();

    // Must STILL use September tariff, not October
    expect((float) $payment->teacher_share)->toBe(750.0);
    expect((float) $payment->academy_share)->toBe(250.0);
    expect((float) $payment->amount_due)->toBe(2000.0);
});

// =========================================================
// TEST C: October payment uses October tariff
// =========================================================

test('october payment uses october tariff', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $group = Group::create([
        'name' => 'Groupe Normal',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'max_students' => 30,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    // September tariff: 2000
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
        'effective_from' => '2026-01-01',
        'effective_to' => '2026-01-31',
        'is_active' => true,
    ]);

    // October tariff: 2500
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 2500,
        'teacher_share' => 1500,
        'academy_share' => 1000,
        'effective_from' => '2026-02-01',
        'effective_to' => null,
        'is_active' => true,
    ]);

    Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'start_date' => '2026-01-01',
        'status' => 'active',
        'payment_type' => 'monthly',
        'school_year_id' => $year->id,
    ]);

    // Payment dated February 1
    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group, [
        'amount_paid' => 1000,
        'payment_date' => '2026-02-01',
    ]));

    $response->assertRedirect(route('payments.index'))
        ->assertSessionHas('success');

    $payment = Payment::first();

    // Must use February tariff: 1000 × (1500/2500) = 600 teacher, 1000 × (1000/2500) = 400 academy
    expect((float) $payment->teacher_share)->toBe(600.0);
    expect((float) $payment->academy_share)->toBe(400.0);
    expect((float) $payment->amount_due)->toBe(2500.0);
});

// =========================================================
// TEST D: No matching tariff must NOT silently use latest tariff
// =========================================================

test('no matching tariff returns error not latest tariff', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $group = Group::create([
        'name' => 'Groupe Normal',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'max_students' => 30,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    // September tariff: 2000
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
        'effective_from' => '2026-09-01',
        'effective_to' => '2026-09-30',
        'is_active' => true,
    ]);

    // October tariff: 2500
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 2500,
        'teacher_share' => 1500,
        'academy_share' => 1000,
        'effective_from' => '2026-10-01',
        'effective_to' => null,
        'is_active' => true,
    ]);

    // Payment dated August — no tariff covers this date
    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group, [
        'amount_paid' => 1000,
        'payment_date' => '2026-08-15',
    ]));

    // Must get a validation error, NOT silently use September or October tariff
    $response->assertSessionHasErrors('group_id');
    expect(Payment::count())->toBe(0);
});

// =========================================================
// TEST E: Backdated store payment uses payment_date tariff
// =========================================================

test('backdated store payment uses payment_date tariff not today', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $group = Group::create([
        'name' => 'Groupe Normal',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'max_students' => 30,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    // September tariff: 2000, teacher=1200, academy=800
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
        'effective_from' => '2026-09-01',
        'effective_to' => '2026-09-30',
        'is_active' => true,
    ]);

    // October tariff: 2500, teacher=1500, academy=1000
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 2500,
        'teacher_share' => 1500,
        'academy_share' => 1000,
        'effective_from' => '2026-10-01',
        'effective_to' => null,
        'is_active' => true,
    ]);

    Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'start_date' => '2026-09-01',
        'status' => 'active',
        'payment_type' => 'monthly',
        'school_year_id' => $year->id,
    ]);

    // Create payment backdated to September (while system date is October)
    $response = $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, $group, [
        'amount_paid' => 1000,
        'payment_date' => '2026-09-15',
    ]));

    $response->assertRedirect(route('payments.index'))
        ->assertSessionHas('success');

    $payment = Payment::first();

    // Must use September tariff snapshot, NOT October
    expect((float) $payment->teacher_share)->toBe(600.0);
    expect((float) $payment->academy_share)->toBe(400.0);
    expect((float) $payment->amount_due)->toBe(2000.0);

    // Verify it's NOT October tariff values
    expect((float) $payment->amount_due)->not->toBe(2500.0);
});

// =========================================================
// TEST F: Payment period != payment_date → revenue uses payment_date
// =========================================================

test('monthly revenue uses actual payment date not period', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $group = Group::create([
        'name' => 'Groupe Normal',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'max_students' => 30,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
        'effective_from' => '2026-01-01',
        'effective_to' => null,
        'is_active' => true,
    ]);

    // Payment for period=September but actually paid in October
    Payment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'group_id' => $group->id,
        'school_year_id' => $year->id,
        'receipt_number' => 'RCP-TEST-F-001',
        'payment_type' => 'monthly',
        'period' => 'Septembre',
        'amount_due' => 2000,
        'amount_paid' => 2000,
        'remaining_amount' => 0,
        'teacher_share' => 1200,
        'academy_share' => 800,
        'payment_method' => 'Espèces',
        'payment_date' => '2026-10-15',
        'payment_time' => '10:00:00',
    ]);

    $revenueService = app(RevenueService::class);

    // Filter by October (month=10): must include this payment
    $octoberPayments = $revenueService->getTeacherPayments($teacher->id, 10, 2026);
    expect($octoberPayments->count())->toBe(1);

    // Filter by September (month=9): must NOT include this payment
    $septemberPayments = $revenueService->getTeacherPayments($teacher->id, 9, 2026);
    expect($septemberPayments->count())->toBe(0);
});
