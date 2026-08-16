<?php

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;

test('la suppression d\'un élève avec des paiements est bloquée', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();

    Payment::factory()->create([
        'student_id' => $student->id,
    ]);

    $response = $this->actingAs($user)->from(route('students.index'))
        ->delete(route('students.destroy', $student));

    $response->assertSessionHasErrors('student');

    expect(Student::find($student->id))->not->toBeNull();
});

test('la suppression d\'un élève avec des présences est bloquée', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();

    Attendance::factory()->create([
        'student_id' => $student->id,
    ]);

    $this->actingAs($user)->from(route('students.index'))
        ->delete(route('students.destroy', $student))
        ->assertSessionHasErrors('student');

    expect(Student::find($student->id))->not->toBeNull();
});

test('la suppression d\'un élève avec des inscriptions est bloquée', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();

    Enrollment::factory()->create([
        'student_id' => $student->id,
    ]);

    $this->actingAs($user)->from(route('students.index'))
        ->delete(route('students.destroy', $student))
        ->assertSessionHasErrors('student');

    expect(Student::find($student->id))->not->toBeNull();
});

test('un élève sans historique est supprimé avec son compte utilisateur', function () {
    $user = User::factory()->create();
    $account = User::factory()->create(['role' => 'student']);

    $student = Student::factory()->create([
        'user_id' => $account->id,
    ]);

    $this->actingAs($user)->delete(route('students.destroy', $student))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect(Student::find($student->id))->toBeNull()
        ->and(User::find($account->id))->toBeNull();
});

test('un élève sans historique ni compte utilisateur est supprimé', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();

    $this->actingAs($user)->delete(route('students.destroy', $student))
        ->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect(Student::find($student->id))->toBeNull();
});
