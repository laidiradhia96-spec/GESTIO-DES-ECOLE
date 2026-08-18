<?php

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentSignalement;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\PaymentSignalementService;

function paymentEnrollment(Student $student, Subject $subject, Teacher $teacher): Enrollment
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

function paymentPayload(Student $student, Subject $subject, array $overrides = []): array
{
    return array_merge([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'payment_type' => 'monthly',
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'payment_method' => 'Espèces',
    ], $overrides);
}

test('store enregistre une date et une heure de paiement antidatées', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    paymentEnrollment($student, $subject, $teacher);

    $year = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, [
        'payment_date' => '2026-08-03',
        'payment_time' => '14:30',
    ]))->assertRedirect(route('payments.index'))
        ->assertSessionHas('success');

    $payment = Payment::first();

    expect($payment->payment_date->format('Y-m-d'))->toBe('2026-08-03')
        ->and($payment->payment_time)->toBe('14:30:00')
        ->and($payment->school_year_id)->toBe($year->id);
});

test('store rejette une date de paiement future', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    paymentEnrollment($student, $subject, $teacher);

    $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject, [
        'payment_date' => now()->addDay()->toDateString(),
    ]))->assertSessionHasErrors('payment_date');

    expect(Payment::count())->toBe(0);
});

test('unpaid ne liste que les paiements non soldés', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();

    $paid = Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
    ]);

    $unpaid = Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
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
    $payment = Payment::factory()->create([
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'payment_type' => 'monthly',
        'period' => '2026-08',
    ]);

    $this->actingAs($user)->get(route('payments.edit', $payment))
        ->assertOk()
        ->assertSee('value="1500.00"', false)
        ->assertSee($payment->receipt_number);
});

test('update recalcule le reste et réconcilie le signalement', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    paymentEnrollment($student, $subject, $teacher);

    $payment = Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'payment_type' => 'monthly',
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1000,
        'remaining_amount' => 500,
        'payment_date' => '2026-08-10',
    ]);

    $service = app(PaymentSignalementService::class);
    $service->syncFromPayment($payment);

    $signalement = PaymentSignalement::where('period', '2026-08')->first();

    expect($signalement)->not->toBeNull()
        ->and((float) $signalement->amount_remaining)->toBe(500.0);

    $this->actingAs($user)->put(route('payments.update', $payment), paymentPayload($student, $subject, [
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'payment_date' => '2026-08-12',
    ]))->assertRedirect(route('payments.index'))
        ->assertSessionHas('success');

    $payment->refresh();

    expect((float) $payment->remaining_amount)->toBe(0.0)
        ->and($payment->payment_date->format('Y-m-d'))->toBe('2026-08-12');

    $signalement->refresh();

    expect($signalement->status)->toBe('resolved')
        ->and((float) $signalement->amount_remaining)->toBe(0.0);
});

test('update rejette un montant payé supérieur au montant demandé', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    paymentEnrollment($student, $subject, $teacher);

    $payment = Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'payment_type' => 'monthly',
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => '2026-08-10',
    ]);

    $this->actingAs($user)->put(route('payments.update', $payment), paymentPayload($student, $subject, [
        'amount_due' => 1500,
        'amount_paid' => 2000,
    ]))->assertSessionHasErrors('amount_paid');

    expect((float) $payment->refresh()->amount_paid)->toBe(1500.0);
});

test('update conserve l\'élève du paiement', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    paymentEnrollment($student, $subject, $teacher);

    $payment = Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'payment_type' => 'monthly',
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => '2026-08-10',
    ]);

    $this->actingAs($user)->put(route('payments.update', $payment), paymentPayload($student, $subject, [
        'amount_paid' => 1000,
    ]))->assertRedirect(route('payments.index'));

    expect($payment->refresh()->student_id)->toBe($student->id);
});

test('store accepte un paiement pour un élève inscrit à la matière', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    paymentEnrollment($student, $subject, $teacher);

    $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject))
        ->assertRedirect(route('payments.index'))
        ->assertSessionHas('success');

    expect(Payment::count())->toBe(1);
});

test('store rejette un paiement pour un élève non inscrit à la matière', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    paymentEnrollment($student, $subject, $teacher);

    $notEnrolled = Student::factory()->create();

    $this->actingAs($user)->post(route('payments.store'), paymentPayload($notEnrolled, $subject))
        ->assertSessionHasErrors('student_id');

    expect(Payment::count())->toBe(0);
});

test('store rejette un paiement pour un élève inscrit à une autre matière', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $enrolledSubject = Subject::factory()->create();
    $otherSubject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    paymentEnrollment($student, $enrolledSubject, $teacher);

    $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $otherSubject))
        ->assertSessionHasErrors('student_id');

    expect(Payment::count())->toBe(0);
});

test('store rejette un paiement pour un élève dont l\'inscription est inactive', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();

    Enrollment::factory()->inactive()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($user)->post(route('payments.store'), paymentPayload($student, $subject))
        ->assertSessionHasErrors('student_id');

    expect(Payment::count())->toBe(0);
});

test('show affiche la matière et l\'enseignant de l\'inscription de l\'élève', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create(['name' => 'Mathématiques']);
    $teacher = Teacher::factory()->create([
        'first_name' => 'Karim',
        'last_name' => 'Amrani',
    ]);

    $otherTeacher = Teacher::factory()->create([
        'first_name' => 'Ali',
        'last_name' => 'Brahimi',
    ]);

    paymentEnrollment($student, $subject, $teacher);

    $subject->teachers()->attach($otherTeacher);

    $payment = Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
    ]);

    $this->actingAs($user)->get(route('payments.show', $payment))
        ->assertOk()
        ->assertSee('Mathématiques')
        ->assertSee('Amrani')
        ->assertDontSee('Brahimi');

    $this->actingAs($user)->get(route('payments.print', $payment))
        ->assertOk()
        ->assertSee('Mathématiques')
        ->assertSee('Amrani')
        ->assertDontSee('Brahimi');
});
