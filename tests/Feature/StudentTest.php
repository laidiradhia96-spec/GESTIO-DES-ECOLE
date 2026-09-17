<?php

use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\Level;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

test('an authenticated user can create a student with enrollments', function () {
    $user = User::factory()->create();

    $level = Level::create([
        'name' => 'Primaire',
        'code' => 'PRI',
        'active' => true,
    ]);

    $subject = Subject::factory()->create([
        'level' => '1AP',
        'primaire' => true,
    ]);

    $teacher = Teacher::factory()->create();
    $teacher->levels()->attach($level);
    $subject->teachers()->attach($teacher);

    $year = SchoolYear::firstOrCreate(
        ['name' => '2025-2026'],
        ['start_date' => '2025-09-01', 'end_date' => '2026-08-31']
    );

    $group = Group::create([
        'name' => 'Groupe Normal',
        'level' => '1AP',
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
    ]);

    $response = $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Ahmed',
        'last_name' => 'Benali',
        'date_of_birth' => '2012-05-10',
        'phone' => '0550 00 00 00',
        'address' => 'Alger',
        'level' => '1AP',
        'parent_name' => 'Mohamed Benali',
        'parent_phone' => '0550 11 11 11',
        'enrollments' => [
            [
                'subject_id' => $subject->id,
                'group_id' => $group->id,
                'payment_type' => 'monthly',
            ],
        ],
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('students.index'));

    $student = Student::where('first_name', 'Ahmed')->first();

    expect($student)->not->toBeNull()
        ->and($student->level)->toBe('1AP')
        ->and($student->address)->toBe('Alger')
        ->and($student->enrollments)->toHaveCount(1)
        ->and($student->enrollments->first()->teacher_id)->toBe($teacher->id)
        ->and($student->groups()->where('groups.id', $group->id)->exists())->toBeTrue();

    $this->assertDatabaseHas('enrollments', [
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'payment_type' => 'monthly',
        'status' => 'active',
    ]);
});

test('student creation is rejected when the group does not match the subject', function () {
    $user = User::factory()->create();

    $subject = Subject::factory()->create([
        'level' => '1AP',
        'primaire' => true,
    ]);

    $otherSubject = Subject::factory()->create([
        'level' => '1AP',
        'primaire' => true,
    ]);

    $teacher = Teacher::factory()->create();
    $otherSubject->teachers()->attach($teacher);

    $year = SchoolYear::firstOrCreate(
        ['name' => '2025-2026'],
        ['start_date' => '2025-09-01', 'end_date' => '2026-08-31']
    );

    $group = Group::create([
        'name' => 'Groupe Autre',
        'level' => '1AP',
        'subject_id' => $otherSubject->id,
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
    ]);

    $this->actingAs($user)
        ->post(route('students.store'), [
            'first_name' => 'Ahmed',
            'last_name' => 'Benali',
            'level' => '1AP',
            'enrollments' => [
                [
                    'subject_id' => $subject->id,
                    'group_id' => $group->id,
                    'payment_type' => 'monthly',
                ],
            ],
        ])
        ->assertSessionHasErrors('enrollments.0.group_id');

    expect(Student::count())->toBe(0);
});

test('student creation is rejected when the subject does not match the level', function () {
    $user = User::factory()->create();

    $subject = Subject::factory()->create([
        'level' => '2AM',
        'moyen' => true,
    ]);
    $teacher = Teacher::factory()->create();
    $subject->teachers()->attach($teacher);

    $year = SchoolYear::firstOrCreate(
        ['name' => '2025-2026'],
        ['start_date' => '2025-09-01', 'end_date' => '2026-08-31']
    );

    $group = Group::create([
        'name' => 'Groupe CEM',
        'level' => '2AM',
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
    ]);

    $this->actingAs($user)
        ->post(route('students.store'), [
            'first_name' => 'Ahmed',
            'last_name' => 'Benali',
            'level' => '1AP',
            'enrollments' => [
                [
                    'subject_id' => $subject->id,
                    'group_id' => $group->id,
                    'payment_type' => 'monthly',
                ],
            ],
        ])
        ->assertSessionHasErrors('enrollments.0.subject_id');

    expect(Student::count())->toBe(0);
});

test('la recherche par nom et prénom retourne l\'élève', function () {
    $user = User::factory()->create();

    Student::factory()->create([
        'first_name' => 'Ahmed',
        'last_name' => 'Benali',
        'phone' => '0550 12 34 56',
    ]);

    Student::factory()->create([
        'first_name' => 'Sara',
        'last_name' => 'Bouzid',
    ]);

    $this->actingAs($user)->get(route('students.index', [
        'search' => 'Benali Ahmed',
    ]))
        ->assertOk()
        ->assertSee('Benali')
        ->assertDontSee('Bouzid');
});

test('la recherche par téléphone retourne l\'élève', function () {
    $user = User::factory()->create();

    Student::factory()->create([
        'first_name' => 'Ahmed',
        'last_name' => 'Benali',
        'phone' => '0550 12 34 56',
    ]);

    Student::factory()->create([
        'first_name' => 'Sara',
        'last_name' => 'Bouzid',
        'phone' => '0661 98 76 54',
    ]);

    $this->actingAs($user)->get(route('students.index', [
        'search' => '0550',
    ]))
        ->assertOk()
        ->assertSee('Benali')
        ->assertDontSee('Bouzid');
});

test('le changement de niveau est bloqué quand une inscription devient incompatible', function () {
    $user = User::factory()->create();

    $level = Level::create([
        'name' => 'Primaire',
        'code' => 'PRI',
        'active' => true,
    ]);

    $subject = Subject::factory()->create([
        'level' => '1AP',
        'primaire' => true,
        'active' => true,
    ]);

    $teacher = Teacher::factory()->create(['active' => true]);
    $teacher->levels()->attach($level);
    $subject->teachers()->attach($teacher);

    $student = Student::factory()->create([
        'first_name' => 'Ahmed',
        'last_name' => 'Benali',
        'level' => '1AP',
    ]);

    $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'payment_type' => 'monthly',
    ]);

    $this->actingAs($user)->put(route('students.update', $student), [
        'first_name' => 'Ahmed',
        'last_name' => 'Benali',
        'date_of_birth' => '2012-05-10',
        'phone' => '0550 00 00 00',
        'address' => 'Alger',
        'level' => '2AM',
        'parent_name' => 'Mohamed Benali',
        'parent_phone' => '0550 11 11 11',
    ])->assertSessionHasErrors('level');

    expect($student->refresh()->level)->toBe('1AP');
});

test('le changement de niveau est autorisé quand les inscriptions restent compatibles', function () {
    $user = User::factory()->create();

    $primaire = Level::create([
        'name' => 'Primaire',
        'code' => 'PRI',
        'active' => true,
    ]);

    $secondaire = Level::create([
        'name' => 'Secondaire',
        'code' => 'SEC',
        'active' => true,
    ]);

    $subject = Subject::factory()->create([
        'level' => '1AP',
        'primaire' => true,
        'lycee' => true,
        'active' => true,
    ]);

    $teacher = Teacher::factory()->create(['active' => true]);
    $teacher->levels()->attach([$primaire, $secondaire]);
    $subject->teachers()->attach($teacher);

    $student = Student::factory()->create([
        'first_name' => 'Ahmed',
        'last_name' => 'Benali',
        'level' => '1AP',
    ]);

    $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'payment_type' => 'monthly',
    ]);

    $this->actingAs($user)->put(route('students.update', $student), [
        'first_name' => 'Ahmed',
        'last_name' => 'Benali',
        'date_of_birth' => '2012-05-10',
        'phone' => '0550 00 00 00',
        'address' => 'Alger',
        'level' => '1AS',
        'parent_name' => 'Mohamed Benali',
        'parent_phone' => '0550 11 11 11',
    ])->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect($student->refresh()->level)->toBe('1AS');
});
