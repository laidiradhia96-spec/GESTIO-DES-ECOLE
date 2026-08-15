<?php

use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

test('un enseignant est créé sans spécialité avec ses matières associées', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create(['active' => true]);

    $this->actingAs($user)->post(route('teachers.store'), [
        'first_name' => 'Ahmed',
        'last_name' => 'Benali',
        'subjects' => [$subject->id],
    ])->assertRedirect(route('teachers.index'))
        ->assertSessionHas('success');

    $teacher = Teacher::where('first_name', 'Ahmed')->first();

    expect($teacher)->not->toBeNull()
        ->and($teacher->subjects->pluck('id'))->toContain($subject->id);
});

test('la modification synchronise les matières de l\'enseignant', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $oldSubject = Subject::factory()->create(['active' => true]);
    $newSubject = Subject::factory()->create(['active' => true]);
    $teacher->subjects()->attach($oldSubject);

    $this->actingAs($user)->put(route('teachers.update', $teacher), [
        'first_name' => 'Ahmed',
        'last_name' => 'Benali',
        'subjects' => [$newSubject->id],
    ])->assertRedirect(route('teachers.index'))
        ->assertSessionHas('success');

    expect($teacher->fresh()->subjects->pluck('id'))
        ->not->toContain($oldSubject->id)
        ->toContain($newSubject->id);
});

test('un enseignant peut être créé sans spécialité et sans matières', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('teachers.store'), [
        'first_name' => 'Sara',
        'last_name' => 'Bouzid',
    ])->assertRedirect(route('teachers.index'));

    expect(Teacher::where('first_name', 'Sara')->exists())->toBeTrue();
});

test('une matière inexistante est rejetée', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('teachers.store'), [
        'first_name' => 'Ahmed',
        'last_name' => 'Benali',
        'subjects' => [9999],
    ])->assertSessionHasErrors('subjects.0');
});
