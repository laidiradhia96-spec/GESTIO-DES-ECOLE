<?php

use App\Models\Student;
use App\Models\User;

test('un visiteur est redirigé vers la connexion pour le tableau de bord', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('un administrateur accède au tableau de bord', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('dashboard'))
        ->assertOk();
});

test('un étudiant ne peut pas accéder au tableau de bord admin', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)->get(route('dashboard'))
        ->assertForbidden();
});

test('un étudiant ne peut pas accéder aux modules admin', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)->get(route('payments.index'))
        ->assertForbidden();

    $this->actingAs($student)->get(route('students.index'))
        ->assertForbidden();

    $this->actingAs($student)->get(route('teachers.index'))
        ->assertForbidden();

    $this->actingAs($student)->get(route('attendances.index'))
        ->assertForbidden();

    $this->actingAs($student)->get(route('payments.unpaid'))
        ->assertForbidden();
});

test('un étudiant accède à son espace élève', function () {
    $studentUser = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create([
        'user_id' => $studentUser->id,
    ]);

    $this->actingAs($studentUser)->get(route('student.dashboard'))
        ->assertOk();
});

test('un administrateur accède à son profil', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('profile.edit'))
        ->assertOk();
});
