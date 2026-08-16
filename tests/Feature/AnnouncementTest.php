<?php

use App\Models\Announcement;
use App\Models\User;

test('la page de modification affiche le type et la case actif', function () {
    $user = User::factory()->create();

    $announcement = Announcement::create([
        'title' => 'Réunion des parents',
        'content' => 'Prévue samedi à 10h.',
        'type' => 'important',
        'is_active' => true,
        'published_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('announcements.edit', $announcement));

    $response->assertOk()
        ->assertSee('name="type"', false)
        ->assertSee('name="is_active"', false);
});

test('update enregistre le type et l\'état actif', function () {
    $user = User::factory()->create();

    $announcement = Announcement::create([
        'title' => 'Réunion des parents',
        'content' => 'Prévue samedi à 10h.',
        'type' => 'info',
        'is_active' => true,
        'published_at' => now(),
    ]);

    $this->actingAs($user)->put(route('announcements.update', $announcement), [
        'title' => 'Réunion reportée',
        'content' => 'Reportée à dimanche.',
        'type' => 'warning',
        'is_active' => '',
    ])->assertRedirect(route('announcements.index'))
        ->assertSessionHas('success');

    $announcement->refresh();

    expect($announcement->title)->toBe('Réunion reportée')
        ->and($announcement->type)->toBe('warning')
        ->and((int) $announcement->is_active)->toBe(0);
});

test('update rejette un type invalide', function () {
    $user = User::factory()->create();

    $announcement = Announcement::create([
        'title' => 'Réunion des parents',
        'content' => 'Prévue samedi à 10h.',
        'type' => 'info',
        'is_active' => true,
        'published_at' => now(),
    ]);

    $this->actingAs($user)->put(route('announcements.update', $announcement), [
        'title' => 'Réunion reportée',
        'content' => 'Reportée à dimanche.',
        'type' => 'urgent',
        'is_active' => '1',
    ])->assertSessionHasErrors('type');

    expect($announcement->refresh()->type)->toBe('info');
});
