<?php

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentSignalement;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

test('la suppression d\'une matière liée à des inscriptions est bloquée', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    Enrollment::factory()->create([
        'subject_id' => $subject->id,
    ]);

    $this->actingAs($user)->from(route('subjects.index'))
        ->delete(route('subjects.destroy', $subject))
        ->assertSessionHas('error');

    expect(Subject::find($subject->id))->not->toBeNull();
});

test('la suppression d\'une matière liée à des présences est bloquée', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    Attendance::factory()->create([
        'subject_id' => $subject->id,
    ]);

    $this->actingAs($user)->from(route('subjects.index'))
        ->delete(route('subjects.destroy', $subject))
        ->assertSessionHas('error');

    expect(Subject::find($subject->id))->not->toBeNull();
});

test('la suppression d\'une matière liée à des séances est bloquée', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();

    ClassSession::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'day' => 'Lundi',
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(1)->toDateString(),
        'status' => 'active',
    ]);

    $this->actingAs($user)->from(route('subjects.index'))
        ->delete(route('subjects.destroy', $subject))
        ->assertSessionHas('error');

    expect(Subject::find($subject->id))->not->toBeNull();
});

test('la suppression d\'une matière liée à des paiements est bloquée', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    Payment::factory()->create([
        'subject_id' => $subject->id,
    ]);

    $this->actingAs($user)->from(route('subjects.index'))
        ->delete(route('subjects.destroy', $subject))
        ->assertSessionHas('error');

    expect(Subject::find($subject->id))->not->toBeNull();
});

test('la suppression d\'une matière liée à des signalements est bloquée', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $student = Student::factory()->create();

    PaymentSignalement::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'period' => now()->format('Y-m'),
        'amount_remaining' => 500,
        'status' => 'pending',
        'signalement_date' => now()->toDateString(),
    ]);

    $this->actingAs($user)->from(route('subjects.index'))
        ->delete(route('subjects.destroy', $subject))
        ->assertSessionHas('error');

    expect(Subject::find($subject->id))->not->toBeNull();
});

test('une matière sans données liées est supprimée', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    $this->actingAs($user)->delete(route('subjects.destroy', $subject))
        ->assertRedirect(route('subjects.index'))
        ->assertSessionHas('success');

    expect(Subject::find($subject->id))->toBeNull();
});