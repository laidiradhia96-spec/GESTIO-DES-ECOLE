<?php

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

test('la suppression d\'un enseignant lié à des inscriptions est bloquée', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();

    Enrollment::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($user)->from(route('teachers.index'))
        ->delete(route('teachers.destroy', $teacher))
        ->assertSessionHas('error');

    expect(Teacher::find($teacher->id))->not->toBeNull();
});

test('la suppression d\'un enseignant lié à des séances est bloquée', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();

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

    $this->actingAs($user)->from(route('teachers.index'))
        ->delete(route('teachers.destroy', $teacher))
        ->assertSessionHas('error');

    expect(Teacher::find($teacher->id))->not->toBeNull();
});

test('la suppression d\'un enseignant lié à des présences est bloquée', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();

    Attendance::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($user)->from(route('teachers.index'))
        ->delete(route('teachers.destroy', $teacher))
        ->assertSessionHas('error');

    expect(Teacher::find($teacher->id))->not->toBeNull();
});

test('un enseignant sans données liées est supprimé et ses niveaux détachés', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $level = Level::create([
        'name' => 'Primaire',
        'code' => 'PRI',
        'active' => true,
    ]);

    $teacher->levels()->attach($level);

    $this->actingAs($user)->delete(route('teachers.destroy', $teacher))
        ->assertRedirect(route('teachers.index'))
        ->assertSessionHas('success');

    expect(Teacher::find($teacher->id))->toBeNull()
        ->and($level->teachers()->count())->toBe(0);
});