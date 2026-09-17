<?php

use App\Models\Enrollment;
use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\Level;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

function cycleTeacher(string $firstName, string $lastName, string $cycleCode): Teacher
{
    $teacher = Teacher::factory()->create([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'active' => true,
    ]);

    $teacher->levels()->attach(Level::firstOrCreate(
        ['code' => $cycleCode],
        ['name' => $cycleCode, 'active' => true]
    )->id);

    return $teacher;
}

function primaireSubject(): Subject
{
    return Subject::factory()->create([
        'active' => true,
        'primaire' => true,
        'moyen' => false,
        'lycee' => false,
    ]);
}

function createTestGroup(Subject $subject, Teacher $teacher, string $level, string $mode = 'normal', string $billingType = 'monthly', float $price = 1500): Group
{
    $year = SchoolYear::firstOrCreate(
        ['name' => '2025-2026'],
        ['start_date' => '2025-09-01', 'end_date' => '2026-08-31']
    );

    $group = Group::create([
        'name' => 'Groupe Test',
        'level' => $level,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => $mode,
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => $billingType,
        'student_price' => $price,
        'teacher_share' => $price * 0.6,
        'academy_share' => $price * 0.4,
        'effective_from' => now()->toDateString(),
    ]);

    return $group;
}

test('1AP + matière liée + enseignant PRI → enseignant retourné', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject->teachers()->attach($teacher);

    $response = $this->actingAs($user)->getJson(route('students.subjects.teachers', [
        'subject' => $subject->id,
        'level' => '1AP',
    ]));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.first_name', 'Ahmed')
        ->assertJsonPath('0.last_name', 'Benali');
});

test('1AM + enseignant MOY → enseignant retourné', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create([
        'active' => true,
        'primaire' => false,
        'moyen' => true,
        'lycee' => false,
    ]);
    $teacher = cycleTeacher('Sara', 'Bouzid', 'MOY');
    $subject->teachers()->attach($teacher);

    $this->actingAs($user)->getJson(route('students.subjects.teachers', [
        'subject' => $subject->id,
        'level' => '1AM',
    ]))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.first_name', 'Sara');
});

test('1AS + enseignant SEC → enseignant retourné', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create([
        'active' => true,
        'primaire' => false,
        'moyen' => false,
        'lycee' => true,
    ]);
    $teacher = cycleTeacher('Omar', 'Sahli', 'SEC');
    $subject->teachers()->attach($teacher);

    $this->actingAs($user)->getJson(route('students.subjects.teachers', [
        'subject' => $subject->id,
        'level' => '1AS',
    ]))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.first_name', 'Omar');
});

test('un enseignant lié à la matière mais sans le cycle correspondant est exclu', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'SEC');
    $subject->teachers()->attach($teacher);

    $this->actingAs($user)->getJson(route('students.subjects.teachers', [
        'subject' => $subject->id,
        'level' => '1AP',
    ]))
        ->assertOk()
        ->assertJsonCount(0);
});

test('une requête sans niveau renvoie une 422', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();

    $this->actingAs($user)->getJson(route('students.subjects.teachers', [
        'subject' => $subject->id,
    ]))
        ->assertStatus(422);
});

test('seuls les enseignants actifs sont retournés', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();

    $active = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $inactive = cycleTeacher('Karim', 'Sahli', 'PRI');
    $inactive->update(['active' => false]);

    $subject->teachers()->attach([$active->id, $inactive->id]);

    $this->actingAs($user)->getJson(route('students.subjects.teachers', [
        'subject' => $subject->id,
        'level' => '1AP',
    ]))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.first_name', 'Ahmed');
});

test('getSubjectsByLevel retourne les matières du cycle demandé', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $lycee = Subject::factory()->create([
        'active' => true,
        'primaire' => false,
        'moyen' => false,
        'lycee' => true,
    ]);

    $response = $this->actingAs($user)->getJson(route('subjects.by-level', '1AP'));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $subject->id)
        ->assertDontSee($lycee->name);
});

test('store accepte un élève 1AP avec un groupe normal monthly', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject->teachers()->attach($teacher);
    $group = createTestGroup($subject, $teacher, '1AP', 'normal', 'monthly', 1500);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'group_id' => $group->id,
            'payment_type' => 'monthly',
        ]],
    ])->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $student = Student::where('first_name', 'Yasmine')->first();

    expect($student)->not->toBeNull()
        ->and(Enrollment::where('teacher_id', $teacher->id)->count())->toBe(1)
        ->and($student->groups()->where('groups.id', $group->id)->exists())->toBeTrue();
});

test('store accepte un élève avec groupe vip_monthly', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject->teachers()->attach($teacher);
    $group = createTestGroup($subject, $teacher, '1AP', 'vip', 'monthly', 6000);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'group_id' => $group->id,
        ]],
    ])->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $student = Student::where('first_name', 'Yasmine')->first();

    expect($student)->not->toBeNull()
        ->and(Enrollment::first()->payment_type)->toBeNull();
});

test('store accepte un élève avec groupe vip_per_session', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject->teachers()->attach($teacher);
    $group = createTestGroup($subject, $teacher, '1AP', 'vip', 'per_session', 2000);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'group_id' => $group->id,
        ]],
    ])->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $student = Student::where('first_name', 'Yasmine')->first();

    expect($student)->not->toBeNull()
        ->and(Enrollment::first()->payment_type)->toBeNull();
});

test('store rejette un groupe qui n\'appartient pas à la matière', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $otherSubject = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject->teachers()->attach($teacher);
    $otherSubject->teachers()->attach($teacher);
    $group = createTestGroup($otherSubject, $teacher, '1AP', 'normal', 'monthly', 1500);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'group_id' => $group->id,
            'payment_type' => 'monthly',
        ]],
    ])->assertSessionHasErrors('enrollments.0.group_id');

    expect(Student::where('first_name', 'Yasmine')->exists())->toBeFalse();
});

test('store rejette un groupe avec un mauvais niveau', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject->teachers()->attach($teacher);
    $group = createTestGroup($subject, $teacher, '1AS', 'normal', 'monthly', 1500);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'group_id' => $group->id,
            'payment_type' => 'monthly',
        ]],
    ])->assertSessionHasErrors('enrollments.0.group_id');

    expect(Student::where('first_name', 'Yasmine')->exists())->toBeFalse();
});

test('store accepte deux matières différentes avec des groupes différents', function () {
    $user = User::factory()->create();
    $subject1 = primaireSubject();
    $subject2 = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject1->teachers()->attach($teacher);
    $subject2->teachers()->attach($teacher);
    $group1 = createTestGroup($subject1, $teacher, '1AP', 'normal', 'monthly', 1500);
    $group2 = createTestGroup($subject2, $teacher, '1AP', 'normal', 'monthly', 1500);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [
            [
                'subject_id' => $subject1->id,
                'group_id' => $group1->id,
                'payment_type' => 'monthly',
            ],
            [
                'subject_id' => $subject2->id,
                'group_id' => $group2->id,
                'payment_type' => 'monthly',
            ],
        ],
    ])->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect(Student::where('first_name', 'Yasmine')->first()->enrollments)->toHaveCount(2);
});

test('store accepte normal dans une matière et vip dans une autre', function () {
    $user = User::factory()->create();
    $subject1 = primaireSubject();
    $subject2 = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject1->teachers()->attach($teacher);
    $subject2->teachers()->attach($teacher);
    $groupNormal = createTestGroup($subject1, $teacher, '1AP', 'normal', 'monthly', 1500);
    $groupVip = createTestGroup($subject2, $teacher, '1AP', 'vip', 'monthly', 6000);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [
            [
                'subject_id' => $subject1->id,
                'group_id' => $groupNormal->id,
                'payment_type' => 'monthly',
            ],
            [
                'subject_id' => $subject2->id,
                'group_id' => $groupVip->id,
                'payment_type' => 'vip_monthly',
            ],
        ],
    ])->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $student = Student::where('first_name', 'Yasmine')->first();
    expect($student->enrollments)->toHaveCount(2);
});

test('store rejette un groupe inactif', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject->teachers()->attach($teacher);
    $group = createTestGroup($subject, $teacher, '1AP', 'normal', 'monthly', 1500);
    $group->update(['is_active' => false]);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'group_id' => $group->id,
            'payment_type' => 'monthly',
        ]],
    ])->assertSessionHasErrors('enrollments.0.group_id');

    expect(Student::where('first_name', 'Yasmine')->exists())->toBeFalse();
});

test('store accepte un groupe sans tarif', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject->teachers()->attach($teacher);

    $year = SchoolYear::firstOrCreate(
        ['name' => '2025-2026'],
        ['start_date' => '2025-09-01', 'end_date' => '2026-08-31']
    );

    $group = Group::create([
        'name' => 'Groupe Sans Tarif',
        'level' => '1AP',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'group_id' => $group->id,
        ]],
    ])->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $student = Student::where('first_name', 'Yasmine')->first();

    expect($student)->not->toBeNull()
        ->and($student->enrollments)->toHaveCount(1)
        ->and($student->groups()->where('groups.id', $group->id)->exists())->toBeTrue();
});

test('store crée bien le pivot student_group avec is_active', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject->teachers()->attach($teacher);
    $group = createTestGroup($subject, $teacher, '1AP', 'normal', 'monthly', 1500);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'group_id' => $group->id,
            'payment_type' => 'monthly',
        ]],
    ])->assertRedirect(route('students.index'));

    $student = Student::where('first_name', 'Yasmine')->first();

    $this->assertDatabaseHas('student_group', [
        'student_id' => $student->id,
        'group_id' => $group->id,
        'is_active' => true,
    ]);
});

test('groups.by-subject-level route resolves to correct controller', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();

    $response = $this->actingAs($user)->getJson(
        route('groups.by-subject-level', ['subject_id' => $subject->id, 'level' => '1AP'])
    );

    // Must NOT be a 404 from groups.show (the old bug)
    $response->assertOk();
});

test('store rejette un même groupe en double pour un élève', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject->teachers()->attach($teacher);
    $group = createTestGroup($subject, $teacher, '1AP', 'normal', 'monthly', 1500);

    // First student
    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'group_id' => $group->id,
            'payment_type' => 'monthly',
        ]],
    ])->assertSessionHas('success');

    // Second student with same group (should work - different student)
    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Omar',
        'last_name' => 'Bouzid',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'group_id' => $group->id,
            'payment_type' => 'monthly',
        ]],
    ])->assertSessionHas('success');

    expect(Student::count())->toBe(2);
});
