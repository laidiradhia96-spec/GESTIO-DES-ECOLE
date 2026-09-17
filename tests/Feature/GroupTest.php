<?php

use App\Models\Group;
use App\Models\GroupSchedule;
use App\Models\GroupTariff;
use App\Models\Level;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

test('l\'index des groupes est accessible', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('groups.index'))->assertOk();
});

test('un groupe est créé avec tarif et créneaux', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['active' => true]);
    $level = Level::create(['name' => '1ère Année Secondaire', 'code' => 'SEC', 'cycle' => 'secondaire', 'primaire' => false, 'moyen' => false, 'lycee' => true]);
    $teacher->levels()->attach($level->id);
    $teacher->subjects()->attach($subject->id);
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $this->actingAs($user)->post(route('groups.store'), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AS',
        'school_year_id' => $year->id,
        'name' => 'Groupe 1',
        'mode' => 'normal',
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'schedules' => [
            ['day' => 'Dimanche', 'start_time' => '09:00', 'end_time' => '10:30', 'room' => 'Salle 01'],
        ],
    ])->assertRedirect(route('groups.index', ['school_year_id' => $year->id]))
        ->assertSessionHas('success');

    $group = Group::where('name', 'Groupe 1')->first();

    expect($group)->not->toBeNull()
        ->and($group->teacher_id)->toBe($teacher->id)
        ->and($group->subject_id)->toBe($subject->id)
        ->and($group->level)->toBe('1AS')
        ->and($group->mode)->toBe('normal');

    expect($group->tariffs)->toHaveCount(1)
        ->and($group->tariffs->first()->student_price)->toBe('1500.00')
        ->and($group->tariffs->first()->teacher_share)->toBe('1000.00')
        ->and($group->tariffs->first()->academy_share)->toBe('500.00');

    expect($group->schedules)->toHaveCount(1)
        ->and($group->schedules->first()->day)->toBe('Dimanche');
});

test('la validation échoue si le prix ne correspond pas à la somme des parts', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['active' => true]);
    $level = Level::create(['name' => '1ère Année Secondaire', 'code' => 'SEC', 'cycle' => 'secondaire', 'primaire' => false, 'moyen' => false, 'lycee' => true]);
    $teacher->levels()->attach($level->id);
    $teacher->subjects()->attach($subject->id);
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $this->actingAs($user)->post(route('groups.store'), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AS',
        'school_year_id' => $year->id,
        'name' => 'Groupe Bad',
        'mode' => 'normal',
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 400,
        'schedules' => [
            ['day' => 'Dimanche', 'start_time' => '09:00', 'end_time' => '10:30'],
        ],
    ])->assertSessionHasErrors('student_price');
});

test('la validation échoue si l\'enseignant n\'est pas lié à la matière', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['active' => true]);
    $level = Level::create(['name' => '1ère Année Secondaire', 'code' => 'SEC', 'cycle' => 'secondaire', 'primaire' => false, 'moyen' => false, 'lycee' => true]);
    $teacher->levels()->attach($level->id);
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $this->actingAs($user)->post(route('groups.store'), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AS',
        'school_year_id' => $year->id,
        'name' => 'Groupe Bad',
        'mode' => 'normal',
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'schedules' => [
            ['day' => 'Dimanche', 'start_time' => '09:00', 'end_time' => '10:30'],
        ],
    ])->assertSessionHasErrors('teacher_id');
});

test('la validation échoue si aucun créneau n\'est fourni', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['active' => true]);
    $level = Level::create(['name' => '1ère Année Secondaire', 'code' => 'SEC', 'cycle' => 'secondaire', 'primaire' => false, 'moyen' => false, 'lycee' => true]);
    $teacher->levels()->attach($level->id);
    $teacher->subjects()->attach($subject->id);
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $this->actingAs($user)->post(route('groups.store'), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AS',
        'school_year_id' => $year->id,
        'name' => 'Groupe Bad',
        'mode' => 'normal',
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'schedules' => [],
    ])->assertSessionHasErrors('schedules');
});

test('le détail d\'un groupe est accessible', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    GroupTariff::factory()->create(['group_id' => $group->id]);
    GroupSchedule::factory()->create(['group_id' => $group->id]);

    $this->actingAs($user)->get(route('groups.show', $group))->assertOk();
});

test('le formulaire de modification est accessible', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();

    $this->actingAs($user)->get(route('groups.edit', $group))->assertOk();
});

test('la mise à jour du tarif crée un nouvel historique', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['active' => true]);
    $level = Level::create(['name' => '1ère Année Secondaire', 'code' => 'SEC', 'cycle' => 'secondaire', 'primaire' => false, 'moyen' => false, 'lycee' => true]);
    $teacher->levels()->attach($level->id);
    $teacher->subjects()->attach($subject->id);
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $group = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AS',
        'school_year_id' => $year->id,
    ]);
    GroupTariff::factory()->create([
        'group_id' => $group->id,
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'billing_type' => 'monthly',
        'is_active' => true,
    ]);

    $this->actingAs($user)->put(route('groups.update', $group), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AS',
        'school_year_id' => $year->id,
        'name' => $group->name,
        'mode' => $group->mode,
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1500,
        'academy_share' => 500,
        'schedules' => [
            ['day' => 'Lundi', 'start_time' => '10:00', 'end_time' => '11:30'],
        ],
    ])->assertRedirect(route('groups.index', ['school_year_id' => $year->id]))
        ->assertSessionHas('success');

    expect($group->tariffs()->count())->toBe(2);
    expect($group->tariffs()->where('is_active', true)->first()->student_price)->toBe('2000.00');
});

test('la désactivation fonctionne', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['is_active' => true]);

    $this->actingAs($user)->patch(route('groups.toggle-status', $group))
        ->assertRedirect();

    expect($group->fresh()->is_active)->toBeFalse();
});

test('la suppression fonctionne', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    GroupTariff::factory()->create(['group_id' => $group->id]);
    GroupSchedule::factory()->create(['group_id' => $group->id]);

    $this->actingAs($user)->delete(route('groups.destroy', $group))
        ->assertRedirect(route('groups.index'))
        ->assertSessionHas('success');

    expect(Group::find($group->id))->toBeNull();
    expect(GroupTariff::where('group_id', $group->id)->exists())->toBeFalse();
    expect(GroupSchedule::where('group_id', $group->id)->exists())->toBeFalse();
});

test('l\'endpoint AJAX matières par enseignant retourne les bonnes matières', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject1 = Subject::factory()->create(['active' => true]);
    $subject2 = Subject::factory()->create(['active' => true]);
    $teacher->subjects()->attach([$subject1->id, $subject2->id]);

    $response = $this->actingAs($user)->getJson(route('groups.subjects-by-teacher', $teacher->id));

    $response->assertOk()->assertJsonCount(2);
});

test('l\'endpoint AJAX niveaux par enseignant retourne les bons niveaux', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $secLevel = Level::create(['name' => 'Secondaire', 'code' => 'SEC', 'cycle' => 'secondaire', 'primaire' => false, 'moyen' => false, 'lycee' => true]);
    $teacher->levels()->attach($secLevel->id);

    $response = $this->actingAs($user)->getJson(route('groups.levels-by-teacher', $teacher->id));

    $response->assertOk()->assertJson(['1AS', '2AS', '3AS']);
});

test('un non-admin est redirigé depuis les routes groupes', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)->get(route('groups.index'))->assertForbidden();
    $this->actingAs($student)->post(route('groups.store'), [])->assertForbidden();
});

test('après création, le groupe apparaît dans la liste des groupes', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['active' => true]);
    $level = Level::create(['name' => '1ère Année Secondaire', 'code' => 'SEC', 'cycle' => 'secondaire', 'primaire' => false, 'moyen' => false, 'lycee' => true]);
    $teacher->levels()->attach($level->id);
    $teacher->subjects()->attach($subject->id);
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $this->actingAs($user)->post(route('groups.store'), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AS',
        'school_year_id' => $year->id,
        'name' => 'Groupe Test',
        'mode' => 'normal',
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'schedules' => [
            ['day' => 'Dimanche', 'start_time' => '08:00', 'end_time' => '10:00'],
        ],
    ])->assertRedirect(route('groups.index', ['school_year_id' => $year->id]));

    $response = $this->actingAs($user)->get(route('groups.index', ['school_year_id' => $year->id]));
    $response->assertOk();
    $response->assertSee('Groupe Test');
    $response->assertSee('1AS');
    $response->assertSee('Normal');
});

test('plusieurs groupes créés apparaissent tous dans la liste', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['active' => true]);
    $level = Level::create(['name' => '1ère Année Secondaire', 'code' => 'SEC', 'cycle' => 'secondaire', 'primaire' => false, 'moyen' => false, 'lycee' => true]);
    $teacher->levels()->attach($level->id);
    $teacher->subjects()->attach($subject->id);
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $slots = [
        ['name' => 'Groupe A', 'day' => 'Dimanche', 'start_time' => '08:00', 'end_time' => '10:00'],
        ['name' => 'Groupe B', 'day' => 'Lundi', 'start_time' => '08:00', 'end_time' => '10:00'],
        ['name' => 'Groupe C', 'day' => 'Mardi', 'start_time' => '08:00', 'end_time' => '10:00'],
    ];

    foreach ($slots as $slot) {
        $this->actingAs($user)->post(route('groups.store'), [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'level' => '1AS',
            'school_year_id' => $year->id,
            'name' => $slot['name'],
            'mode' => 'normal',
            'effective_from' => '2026-09-15',
            'billing_type' => 'monthly',
            'student_price' => 1500,
            'teacher_share' => 1000,
            'academy_share' => 500,
            'schedules' => [
                ['day' => $slot['day'], 'start_time' => $slot['start_time'], 'end_time' => $slot['end_time']],
            ],
        ])->assertRedirect();
    }

    $response = $this->actingAs($user)->get(route('groups.index', ['school_year_id' => $year->id]));
    $response->assertOk();
    $response->assertSee('Groupe A');
    $response->assertSee('Groupe B');
    $response->assertSee('Groupe C');
    $this->assertDatabaseCount('groups', 3);
});

test('la redirection après création inclut school_year_id', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['active' => true]);
    $level = Level::create(['name' => '1ère Année Secondaire', 'code' => 'SEC', 'cycle' => 'secondaire', 'primaire' => false, 'moyen' => false, 'lycee' => true]);
    $teacher->levels()->attach($level->id);
    $teacher->subjects()->attach($subject->id);
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $response = $this->actingAs($user)->post(route('groups.store'), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AS',
        'school_year_id' => $year->id,
        'name' => 'Groupe Redirect',
        'mode' => 'vip',
        'effective_from' => '2026-09-15',
        'billing_type' => 'per_session',
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
        'schedules' => [
            ['day' => 'Lundi', 'start_time' => '14:00', 'end_time' => '16:00'],
        ],
    ]);

    $response->assertRedirect(route('groups.index', ['school_year_id' => $year->id]));
});

test('le groupe créé existe bien en base avec toutes les données', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['active' => true]);
    $level = Level::create(['name' => '1ère Année Secondaire', 'code' => 'SEC', 'cycle' => 'secondaire', 'primaire' => false, 'moyen' => false, 'lycee' => true]);
    $teacher->levels()->attach($level->id);
    $teacher->subjects()->attach($subject->id);
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $this->actingAs($user)->post(route('groups.store'), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AS',
        'school_year_id' => $year->id,
        'name' => 'Groupe Complet',
        'mode' => 'special',
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 3000,
        'teacher_share' => 2000,
        'academy_share' => 1000,
        'schedules' => [
            ['day' => 'Mercredi', 'start_time' => '10:00', 'end_time' => '12:00', 'room' => 'Salle 03'],
        ],
    ]);

    $group = Group::latest()->first();

    expect($group)->not->toBeNull()
        ->and($group->name)->toBe('Groupe Complet')
        ->and($group->teacher_id)->toBe($teacher->id)
        ->and($group->subject_id)->toBe($subject->id)
        ->and($group->level)->toBe('1AS')
        ->and($group->school_year_id)->toBe($year->id)
        ->and($group->mode)->toBe('special')
        ->and($group->is_active)->toBeTrue();

    expect($group->tariffs)->toHaveCount(1)
        ->and($group->tariffs->first()->student_price)->toBe('3000.00')
        ->and($group->tariffs->first()->billing_type)->toBe('monthly')
        ->and($group->tariffs->first()->is_active)->toBeTrue();

    expect($group->schedules)->toHaveCount(1)
        ->and($group->schedules->first()->day)->toBe('Mercredi')
        ->and($group->schedules->first()->start_time)->toBe('10:00')
        ->and($group->schedules->first()->room)->toBe('Salle 03');
});

// =====================================================
// AJAX ENDPOINT: groups.by-subject-level
// =====================================================

test('ajax by-subject-level returns matching active groups', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['active' => true, 'lycee' => true]);
    $teacher->subjects()->attach($subject->id);
    $year = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
        'is_current' => true,
    ]);

    $group = Group::create([
        'name' => 'GRP 1',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 900,
        'academy_share' => 600,
        'effective_from' => now()->toDateString(),
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->getJson(
        route('groups.by-subject-level', ['subject_id' => $subject->id, 'level' => '1AS'])
    );

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $group->id)
        ->assertJsonPath('0.name', 'GRP 1');
});

test('ajax by-subject-level excludes groups for different subject', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject1 = Subject::factory()->create(['active' => true, 'lycee' => true]);
    $subject2 = Subject::factory()->create(['active' => true, 'lycee' => true]);
    $teacher->subjects()->attach([$subject1->id, $subject2->id]);
    $year = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
        'is_current' => true,
    ]);

    Group::create([
        'name' => 'GRP S1',
        'level' => '1AS',
        'subject_id' => $subject1->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->getJson(
        route('groups.by-subject-level', ['subject_id' => $subject2->id, 'level' => '1AS'])
    );

    $response->assertOk()->assertJsonCount(0);
});

test('ajax by-subject-level excludes groups for different level', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['active' => true, 'lycee' => true]);
    $teacher->subjects()->attach($subject->id);
    $year = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
        'is_current' => true,
    ]);

    Group::create([
        'name' => 'GRP 2AS',
        'level' => '2AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->getJson(
        route('groups.by-subject-level', ['subject_id' => $subject->id, 'level' => '1AS'])
    );

    $response->assertOk()->assertJsonCount(0);
});

test('ajax by-subject-level excludes groups from different school year', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['active' => true, 'lycee' => true]);
    $teacher->subjects()->attach($subject->id);

    $currentYear = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
    ]);

    $oldYear = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    Group::create([
        'name' => 'GRP Old',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $oldYear->id,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->getJson(
        route('groups.by-subject-level', ['subject_id' => $subject->id, 'level' => '1AS'])
    );

    $response->assertOk()->assertJsonCount(0);
});

test('ajax by-subject-level excludes inactive groups', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['active' => true, 'lycee' => true]);
    $teacher->subjects()->attach($subject->id);
    $year = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
        'is_current' => true,
    ]);

    Group::create([
        'name' => 'GRP Inactive',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'normal',
        'is_active' => false,
    ]);

    $response = $this->actingAs($user)->getJson(
        route('groups.by-subject-level', ['subject_id' => $subject->id, 'level' => '1AS'])
    );

    $response->assertOk()->assertJsonCount(0);
});

test('ajax by-subject-level response contains teacher', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create([
        'first_name' => 'Radhia',
        'last_name' => 'Laidi',
    ]);
    $subject = Subject::factory()->create(['active' => true, 'lycee' => true]);
    $teacher->subjects()->attach($subject->id);
    $year = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
        'is_current' => true,
    ]);

    $group = Group::create([
        'name' => 'GRP 1',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->getJson(
        route('groups.by-subject-level', ['subject_id' => $subject->id, 'level' => '1AS'])
    );

    $response->assertOk()
        ->assertJsonPath('0.teacher.first_name', 'Radhia')
        ->assertJsonPath('0.teacher.last_name', 'Laidi');
});

test('ajax by-subject-level response contains current tariff', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['active' => true, 'lycee' => true]);
    $teacher->subjects()->attach($subject->id);
    $year = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
        'is_current' => true,
    ]);

    $group = Group::create([
        'name' => 'GRP 1',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'vip',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 6000,
        'teacher_share' => 4000,
        'academy_share' => 2000,
        'effective_from' => now()->toDateString(),
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->getJson(
        route('groups.by-subject-level', ['subject_id' => $subject->id, 'level' => '1AS'])
    );

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.tariffs.0.billing_type', 'monthly')
        ->assertJsonPath('0.tariffs.0.student_price', '6000.00');
});

test('ajax by-subject-level excludes expired tariffs', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['active' => true, 'lycee' => true]);
    $teacher->subjects()->attach($subject->id);
    $year = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
        'is_current' => true,
    ]);

    $group = Group::create([
        'name' => 'GRP 1',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    // Expired tariff
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 900,
        'academy_share' => 600,
        'effective_from' => '2025-09-01',
        'effective_to' => '2026-06-30',
        'is_active' => true,
    ]);

    // Active tariff
    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1200,
        'academy_share' => 800,
        'effective_from' => now()->toDateString(),
        'effective_to' => null,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->getJson(
        route('groups.by-subject-level', ['subject_id' => $subject->id, 'level' => '1AS'])
    );

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.tariffs.0.student_price', '2000.00');
});

test('modifier le niveau dun groupe enregistre le changement', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $teacher->subjects()->attach($subject->id);
    $level = Level::where('code', 'MOY')->firstOrCreate(['code' => 'MOY', 'name' => 'Moyen', 'active' => true]);
    $teacher->levels()->attach($level->id);

    $group = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
    ]);
    GroupTariff::factory()->create([
        'group_id' => $group->id,
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'billing_type' => 'monthly',
        'is_active' => true,
    ]);

    $this->actingAs($user)->put(route('groups.update', $group), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '2AM',
        'school_year_id' => $year->id,
        'name' => $group->name,
        'mode' => $group->mode,
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'schedules' => [
            ['day' => 'Lundi', 'start_time' => '10:00', 'end_time' => '11:30'],
        ],
    ])->assertRedirect(route('groups.index', ['school_year_id' => $year->id]))
        ->assertSessionHas('success');

    expect($group->fresh()->level)->toBe('2AM');
    expect($group->fresh()->teacher_id)->toBe($teacher->id);
    expect($group->fresh()->subject_id)->toBe($subject->id);
});

test('modifier le niveau dun groupe avec creneaux existants enregistre le changement', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $teacher->subjects()->attach($subject->id);
    $level = Level::where('code', 'MOY')->firstOrCreate(['code' => 'MOY', 'name' => 'Moyen', 'active' => true]);
    $teacher->levels()->attach($level->id);

    $group = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
    ]);
    GroupTariff::factory()->create([
        'group_id' => $group->id,
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'billing_type' => 'monthly',
        'is_active' => true,
    ]);
    GroupSchedule::factory()->create([
        'group_id' => $group->id,
        'day' => 'Lundi',
        'start_time' => '14:00',
        'end_time' => '16:00',
    ]);

    $response = $this->actingAs($user)->put(route('groups.update', $group), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '2AM',
        'school_year_id' => $year->id,
        'name' => $group->name,
        'mode' => $group->mode,
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'schedules' => [
            ['day' => 'Lundi', 'start_time' => '14:00', 'end_time' => '16:00'],
        ],
    ]);

    $response->assertRedirect(route('groups.index', ['school_year_id' => $year->id]))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'level' => '2AM',
    ]);
});

test('modifier un creneau sans conflit enregistre le changement', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $teacher->subjects()->attach($subject->id);
    $level = Level::where('code', 'MOY')->firstOrCreate(['code' => 'MOY', 'name' => 'Moyen', 'active' => true]);
    $teacher->levels()->attach($level->id);

    $group = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
    ]);
    GroupTariff::factory()->create([
        'group_id' => $group->id,
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'billing_type' => 'monthly',
        'is_active' => true,
    ]);
    GroupSchedule::factory()->create([
        'group_id' => $group->id,
        'day' => 'Lundi',
        'start_time' => '14:00',
        'end_time' => '16:00',
    ]);

    $response = $this->actingAs($user)->put(route('groups.update', $group), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
        'name' => $group->name,
        'mode' => $group->mode,
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'schedules' => [
            ['day' => 'Mardi', 'start_time' => '15:00', 'end_time' => '17:00'],
        ],
    ]);

    $response->assertRedirect(route('groups.index', ['school_year_id' => $year->id]))
        ->assertSessionHas('success');

    $schedules = $group->fresh()->schedules;
    expect($schedules)->toHaveCount(1);
    expect($schedules->first()->day)->toBe('Mardi');
    expect($schedules->first()->start_time)->toBe('15:00');
    expect($schedules->first()->end_time)->toBe('17:00');
});

test('ajouter un deuxieme creneau lors de la modification', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $teacher->subjects()->attach($subject->id);
    $level = Level::where('code', 'MOY')->firstOrCreate(['code' => 'MOY', 'name' => 'Moyen', 'active' => true]);
    $teacher->levels()->attach($level->id);

    $group = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
    ]);
    GroupTariff::factory()->create([
        'group_id' => $group->id,
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'billing_type' => 'monthly',
        'is_active' => true,
    ]);
    GroupSchedule::factory()->create([
        'group_id' => $group->id,
        'day' => 'Lundi',
        'start_time' => '14:00',
        'end_time' => '16:00',
    ]);

    $response = $this->actingAs($user)->put(route('groups.update', $group), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
        'name' => $group->name,
        'mode' => $group->mode,
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'schedules' => [
            ['day' => 'Lundi', 'start_time' => '14:00', 'end_time' => '16:00'],
            ['day' => 'Mercredi', 'start_time' => '15:00', 'end_time' => '17:00'],
        ],
    ]);

    $response->assertRedirect(route('groups.index', ['school_year_id' => $year->id]))
        ->assertSessionHas('success');

    expect($group->fresh()->schedules)->toHaveCount(2);
});

test('supprimer un creneau lors de la modification', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $teacher->subjects()->attach($subject->id);
    $level = Level::where('code', 'MOY')->firstOrCreate(['code' => 'MOY', 'name' => 'Moyen', 'active' => true]);
    $teacher->levels()->attach($level->id);

    $group = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
    ]);
    GroupTariff::factory()->create([
        'group_id' => $group->id,
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'billing_type' => 'monthly',
        'is_active' => true,
    ]);
    GroupSchedule::factory()->create([
        'group_id' => $group->id,
        'day' => 'Lundi',
        'start_time' => '14:00',
        'end_time' => '16:00',
    ]);
    GroupSchedule::factory()->create([
        'group_id' => $group->id,
        'day' => 'Mercredi',
        'start_time' => '15:00',
        'end_time' => '17:00',
    ]);

    expect($group->schedules)->toHaveCount(2);

    $response = $this->actingAs($user)->put(route('groups.update', $group), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
        'name' => $group->name,
        'mode' => $group->mode,
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'schedules' => [
            ['day' => 'Lundi', 'start_time' => '14:00', 'end_time' => '16:00'],
        ],
    ]);

    $response->assertRedirect(route('groups.index', ['school_year_id' => $year->id]))
        ->assertSessionHas('success');

    expect($group->fresh()->schedules)->toHaveCount(1);
    expect($group->fresh()->schedules->first()->day)->toBe('Lundi');
});

test('modifier plusieurs informations simultanement', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $teacher->subjects()->attach($subject->id);
    $level = Level::where('code', 'MOY')->firstOrCreate(['code' => 'MOY', 'name' => 'Moyen', 'active' => true]);
    $teacher->levels()->attach($level->id);

    $group = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
        'name' => 'Ancien Nom',
        'mode' => 'normal',
    ]);
    GroupTariff::factory()->create([
        'group_id' => $group->id,
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'billing_type' => 'monthly',
        'is_active' => true,
    ]);
    GroupSchedule::factory()->create([
        'group_id' => $group->id,
        'day' => 'Lundi',
        'start_time' => '14:00',
        'end_time' => '16:00',
    ]);

    $response = $this->actingAs($user)->put(route('groups.update', $group), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '2AM',
        'school_year_id' => $year->id,
        'name' => 'Nouveau Nom',
        'mode' => 'vip',
        'effective_from' => '2026-09-15',
        'billing_type' => 'per_session',
        'student_price' => 3000,
        'teacher_share' => 2000,
        'academy_share' => 1000,
        'schedules' => [
            ['day' => 'Mardi', 'start_time' => '09:00', 'end_time' => '11:00'],
        ],
    ]);

    $response->assertRedirect(route('groups.index', ['school_year_id' => $year->id]))
        ->assertSessionHas('success');

    $fresh = $group->fresh();
    expect($fresh->name)->toBe('Nouveau Nom');
    expect($fresh->level)->toBe('2AM');
    expect($fresh->mode)->toBe('vip');

    $activeTariff = $fresh->tariffs()->where('is_active', true)->first();
    expect($activeTariff->student_price)->toBe('3000.00');
    expect($activeTariff->billing_type)->toBe('per_session');

    expect($fresh->schedules)->toHaveCount(1);
    expect($fresh->schedules->first()->day)->toBe('Mardi');
});

test('un vrai conflit avec un autre groupe echoue la validation', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $teacher->subjects()->attach($subject->id);
    $level = Level::where('code', 'MOY')->firstOrCreate(['code' => 'MOY', 'name' => 'Moyen', 'active' => true]);
    $teacher->levels()->attach($level->id);

    $group2 = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
        'name' => 'Groupe 2',
    ]);
    GroupTariff::factory()->create(['group_id' => $group2->id, 'is_active' => true, 'student_price' => 1500, 'teacher_share' => 1000, 'academy_share' => 500, 'billing_type' => 'monthly']);
    GroupSchedule::factory()->create([
        'group_id' => $group2->id,
        'day' => 'Lundi',
        'start_time' => '14:00',
        'end_time' => '16:00',
    ]);

    $group3 = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
        'name' => 'Groupe 3',
    ]);
    GroupTariff::factory()->create(['group_id' => $group3->id, 'is_active' => true, 'student_price' => 1500, 'teacher_share' => 1000, 'academy_share' => 500, 'billing_type' => 'monthly']);
    GroupSchedule::factory()->create([
        'group_id' => $group3->id,
        'day' => 'Lundi',
        'start_time' => '14:00',
        'end_time' => '16:00',
    ]);

    $response = $this->actingAs($user)->put(route('groups.update', $group2), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
        'name' => $group2->name,
        'mode' => $group2->mode,
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'schedules' => [
            ['day' => 'Lundi', 'start_time' => '14:00', 'end_time' => '16:00'],
        ],
    ]);

    $response->assertSessionHasErrors('schedules.0.start_time');
});

test('apres modification teacher_id et subject_id restent inchanges', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]);

    $teacher->subjects()->attach($subject->id);
    $level = Level::where('code', 'MOY')->firstOrCreate(['code' => 'MOY', 'name' => 'Moyen', 'active' => true]);
    $teacher->levels()->attach($level->id);

    $group = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
    ]);
    GroupTariff::factory()->create([
        'group_id' => $group->id,
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'billing_type' => 'monthly',
        'is_active' => true,
    ]);
    GroupSchedule::factory()->create([
        'group_id' => $group->id,
        'day' => 'Lundi',
        'start_time' => '14:00',
        'end_time' => '16:00',
    ]);

    $this->actingAs($user)->put(route('groups.update', $group), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '2AM',
        'school_year_id' => $year->id,
        'name' => $group->name,
        'mode' => $group->mode,
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'schedules' => [
            ['day' => 'Lundi', 'start_time' => '14:00', 'end_time' => '16:00'],
        ],
    ]);

    expect($group->fresh()->teacher_id)->toBe($teacher->id);
    expect($group->fresh()->subject_id)->toBe($subject->id);
});

// =====================================================
// NEW TESTS — effective_from → school_year_id workflow
// =====================================================

test('group name update persists and appears in index', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_current' => true]);

    $teacher->subjects()->attach($subject->id);
    $level = Level::where('code', 'MOY')->firstOrCreate(['code' => 'MOY', 'name' => 'Moyen', 'active' => true]);
    $teacher->levels()->attach($level->id);

    $group = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
        'name' => 'GRP 2',
    ]);
    GroupTariff::factory()->create([
        'group_id' => $group->id,
        'student_price' => 2000,
        'teacher_share' => 1500,
        'academy_share' => 500,
        'billing_type' => 'monthly',
        'is_active' => true,
    ]);

    $this->actingAs($user)->put(route('groups.update', $group), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
        'name' => 'GRP 2 TEST',
        'mode' => 'normal',
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 2000,
        'teacher_share' => 1500,
        'academy_share' => 500,
        'schedules' => [
            ['day' => 'Dimanche', 'start_time' => '09:00', 'end_time' => '10:30'],
        ],
    ])->assertRedirect(route('groups.index', ['school_year_id' => $year->id]));

    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'name' => 'GRP 2 TEST',
    ]);

    $response = $this->actingAs($user)->get(route('groups.index', ['school_year_id' => $year->id]));
    $response->assertOk();
    $response->assertSee('GRP 2 TEST');
});

test('group level update persists with same group id', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_current' => true]);

    $teacher->subjects()->attach($subject->id);
    $level = Level::where('code', 'MOY')->firstOrCreate(['code' => 'MOY', 'name' => 'Moyen', 'active' => true]);
    $teacher->levels()->attach($level->id);

    $group = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
    ]);
    GroupTariff::factory()->create([
        'group_id' => $group->id,
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'billing_type' => 'monthly',
        'is_active' => true,
    ]);

    $this->actingAs($user)->put(route('groups.update', $group), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '2AM',
        'school_year_id' => $year->id,
        'name' => $group->name,
        'mode' => $group->mode,
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'schedules' => [
            ['day' => 'Dimanche', 'start_time' => '09:00', 'end_time' => '10:30'],
        ],
    ])->assertRedirect(route('groups.index', ['school_year_id' => $year->id]));

    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'level' => '2AM',
    ]);
    expect($group->fresh()->id)->toBe($group->id);
});

test('effective_from in different school year resolves correct school_year_id', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $year1 = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_current' => true]);
    $year2 = SchoolYear::create(['name' => '2027-2028', 'start_date' => '2027-09-01', 'end_date' => '2028-08-31', 'is_current' => false]);

    $teacher->subjects()->attach($subject->id);
    $level = Level::where('code', 'MOY')->firstOrCreate(['code' => 'MOY', 'name' => 'Moyen', 'active' => true]);
    $teacher->levels()->attach($level->id);

    $group = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year1->id,
    ]);
    GroupTariff::factory()->create([
        'group_id' => $group->id,
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'billing_type' => 'monthly',
        'is_active' => true,
    ]);

    $this->actingAs($user)->put(route('groups.update', $group), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year1->id,
        'name' => $group->name,
        'mode' => $group->mode,
        'effective_from' => '2027-09-10',
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'schedules' => [
            ['day' => 'Dimanche', 'start_time' => '09:00', 'end_time' => '10:30'],
        ],
    ])->assertRedirect(route('groups.index', ['school_year_id' => $year2->id]));

    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'school_year_id' => $year2->id,
    ]);
});

test('unchanged tariff does not create another tariff', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_current' => true]);

    $teacher->subjects()->attach($subject->id);
    $level = Level::where('code', 'MOY')->firstOrCreate(['code' => 'MOY', 'name' => 'Moyen', 'active' => true]);
    $teacher->levels()->attach($level->id);

    $group = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
    ]);
    GroupTariff::factory()->create([
        'group_id' => $group->id,
        'student_price' => 2000,
        'teacher_share' => 1500,
        'academy_share' => 500,
        'billing_type' => 'monthly',
        'is_active' => true,
    ]);

    $this->actingAs($user)->put(route('groups.update', $group), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
        'name' => $group->name,
        'mode' => $group->mode,
        'effective_from' => '2026-09-15',
        'billing_type' => 'monthly',
        'student_price' => '2000',
        'teacher_share' => '1500',
        'academy_share' => '500',
        'schedules' => [
            ['day' => 'Dimanche', 'start_time' => '09:00', 'end_time' => '10:30'],
        ],
    ]);

    expect($group->tariffs()->count())->toBe(1);
    expect($group->tariffs()->where('is_active', true)->first()->student_price)->toBe('2000.00');
});

test('changed tariff creates new tariff with correct effective_from', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $year1 = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_current' => true]);
    $year2 = SchoolYear::create(['name' => '2027-2028', 'start_date' => '2027-09-01', 'end_date' => '2028-08-31', 'is_current' => false]);

    $teacher->subjects()->attach($subject->id);
    $level = Level::where('code', 'MOY')->firstOrCreate(['code' => 'MOY', 'name' => 'Moyen', 'active' => true]);
    $teacher->levels()->attach($level->id);

    $group = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year1->id,
    ]);
    GroupTariff::factory()->create([
        'group_id' => $group->id,
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'billing_type' => 'monthly',
        'is_active' => true,
    ]);

    $this->actingAs($user)->put(route('groups.update', $group), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year1->id,
        'name' => $group->name,
        'mode' => $group->mode,
        'effective_from' => '2027-09-10',
        'billing_type' => 'monthly',
        'student_price' => 3000,
        'teacher_share' => 2000,
        'academy_share' => 1000,
        'schedules' => [
            ['day' => 'Dimanche', 'start_time' => '09:00', 'end_time' => '10:30'],
        ],
    ]);

    expect($group->tariffs()->count())->toBe(2);
    $active = $group->tariffs()->where('is_active', true)->first();
    expect($active->student_price)->toBe('3000.00');
    expect($active->effective_from->format('Y-m-d'))->toBe('2027-09-10');
});

test('invalid effective_from outside all school years fails validation', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_current' => true]);

    $teacher->subjects()->attach($subject->id);
    $level = Level::where('code', 'MOY')->firstOrCreate(['code' => 'MOY', 'name' => 'Moyen', 'active' => true]);
    $teacher->levels()->attach($level->id);

    $group = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
        'name' => 'Original Name',
    ]);
    GroupTariff::factory()->create([
        'group_id' => $group->id,
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'billing_type' => 'monthly',
        'is_active' => true,
    ]);

    $this->actingAs($user)->put(route('groups.update', $group), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
        'name' => 'Should Not Change',
        'mode' => 'normal',
        'effective_from' => '2028-12-25',
        'billing_type' => 'monthly',
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'schedules' => [
            ['day' => 'Dimanche', 'start_time' => '09:00', 'end_time' => '10:30'],
        ],
    ])->assertSessionHasErrors('effective_from');

    expect($group->fresh()->name)->toBe('Original Name');
    expect($group->fresh()->school_year_id)->toBe($year->id);
    expect($group->tariffs()->count())->toBe(1);
});

test('group id is preserved after update', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $year = SchoolYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_current' => true]);

    $teacher->subjects()->attach($subject->id);
    $level = Level::where('code', 'MOY')->firstOrCreate(['code' => 'MOY', 'name' => 'Moyen', 'active' => true]);
    $teacher->levels()->attach($level->id);

    $group = Group::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '1AM',
        'school_year_id' => $year->id,
    ]);
    GroupTariff::factory()->create([
        'group_id' => $group->id,
        'student_price' => 1500,
        'teacher_share' => 1000,
        'academy_share' => 500,
        'billing_type' => 'monthly',
        'is_active' => true,
    ]);

    $originalId = $group->id;

    $this->actingAs($user)->put(route('groups.update', $group), [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'level' => '2AM',
        'school_year_id' => $year->id,
        'name' => 'Updated Name',
        'mode' => 'vip',
        'effective_from' => '2026-09-15',
        'billing_type' => 'per_session',
        'student_price' => 3000,
        'teacher_share' => 2000,
        'academy_share' => 1000,
        'schedules' => [
            ['day' => 'Mardi', 'start_time' => '10:00', 'end_time' => '12:00'],
        ],
    ]);

    $this->assertDatabaseHas('groups', [
        'id' => $originalId,
        'name' => 'Updated Name',
        'level' => '2AM',
        'mode' => 'vip',
    ]);
});
