<?php

use App\Models\User;

test('un compte créé via register reçoit le rôle student', function () {
    $this->post(route('register'), [
        'name' => 'Nouvel Utilisateur',
        'email' => 'nouveau@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('dashboard'));

    $user = User::where('email', 'nouveau@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('student');
});

test('un rôle admin injecté dans register est ignoré', function () {
    $this->post(route('register'), [
        'name' => 'Nouvel Utilisateur',
        'email' => 'nouveau@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin',
    ])->assertRedirect(route('dashboard'));

    $user = User::where('email', 'nouveau@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('student');
});

test('un admin existant accède aux routes admin', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('dashboard'))
        ->assertOk();
});

test('un étudiant ne peut pas accéder aux routes admin', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)->get(route('dashboard'))
        ->assertForbidden();
});
