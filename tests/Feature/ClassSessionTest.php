<?php

use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

function sessionPayload(Student $student, Subject $subject, Teacher $teacher, array $overrides = []): array
{
    return array_merge([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'day' => 'Lundi',
        'start_time' => '09:00',
        'end_time' => '10:00',
        'start_date' => '2026-09-01',
        'end_date' => '2026-12-31',
        'status' => 'active',
    ], $overrides);
}

function activeSessionEnrollment(Student $student, Subject $subject, Teacher $teacher): Enrollment
{
    return Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'payment_type' => 'monthly',
    ]);
}

test('store accepte une séance pour un élève inscrit à la matière avec l\'enseignant', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    activeSessionEnrollment($student, $subject, $teacher);

    $year = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-08-31',
    ]);

    $this->actingAs($user)->post(route('class-sessions.store'), sessionPayload($student, $subject, $teacher))
        ->assertRedirect(route('class-sessions.index'))
        ->assertSessionHas('success');

    expect(ClassSession::count())->toBe(1)
        ->and(ClassSession::first()->school_year_id)->toBe($year->id);
});

test('store rejette une séance pour un élève non inscrit', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    activeSessionEnrollment($student, $subject, $teacher);

    $notEnrolled = Student::factory()->create();

    $this->actingAs($user)->post(route('class-sessions.store'), sessionPayload($notEnrolled, $subject, $teacher))
        ->assertSessionHasErrors('student_id');

    expect(ClassSession::count())->toBe(0);
});

test('store rejette une séance dont l\'enseignant ne correspond pas à l\'inscription', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $otherTeacher = Teacher::factory()->create();
    activeSessionEnrollment($student, $subject, $teacher);

    $this->actingAs($user)->post(route('class-sessions.store'), sessionPayload($student, $subject, $otherTeacher))
        ->assertSessionHasErrors('student_id');

    expect(ClassSession::count())->toBe(0);
});

test('store rejette une séance pour un élève inscrit à une autre matière', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $enrolledSubject = Subject::factory()->create();
    $otherSubject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    activeSessionEnrollment($student, $enrolledSubject, $teacher);

    $this->actingAs($user)->post(route('class-sessions.store'), sessionPayload($student, $otherSubject, $teacher))
        ->assertSessionHasErrors('student_id');

    expect(ClassSession::count())->toBe(0);
});

test('store rejette une séance pour un élève dont l\'inscription est inactive', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();

    Enrollment::factory()->inactive()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($user)->post(route('class-sessions.store'), sessionPayload($student, $subject, $teacher))
        ->assertSessionHasErrors('student_id');

    expect(ClassSession::count())->toBe(0);
});
