<?php

use App\Models\Enrollment;
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

test('store accepte un élève 1AP avec un enseignant lié à la matière et au cycle PRI', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject->teachers()->attach($teacher);

    $year = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'payment_type' => 'monthly',
        ]],
    ])->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    $student = Student::where('first_name', 'Yasmine')->first();

    expect($student)->not->toBeNull()
        ->and(Enrollment::where('teacher_id', $teacher->id)->count())->toBe(1)
        ->and(Enrollment::where('teacher_id', $teacher->id)->first()->school_year_id)->toBe($year->id);
});

test('store rejette un enseignant lié à une autre matière', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $otherSubject = Subject::factory()->create([
        'active' => true,
        'primaire' => true,
        'moyen' => false,
        'lycee' => false,
    ]);
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $otherSubject->teachers()->attach($teacher);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'payment_type' => 'monthly',
        ]],
    ])->assertSessionHasErrors('enrollments.0.teacher_id');

    expect(Student::where('first_name', 'Yasmine')->exists())->toBeFalse();
});

test('store rejette un enseignant actif lié à la matière mais avec un mauvais cycle', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $teacher = cycleTeacher('Omar', 'Sahli', 'SEC');
    $subject->teachers()->attach($teacher);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'payment_type' => 'monthly',
        ]],
    ])->assertSessionHasErrors('enrollments.0.teacher_id');

    expect(Student::where('first_name', 'Yasmine')->exists())->toBeFalse();
});

test('store rejette un enseignant inactif même lié à la matière et au bon cycle', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $teacher = cycleTeacher('Karim', 'Sahli', 'PRI');
    $teacher->update(['active' => false]);
    $subject->teachers()->attach($teacher);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'payment_type' => 'monthly',
        ]],
    ])->assertSessionHasErrors('enrollments.0.teacher_id');

    expect(Student::where('first_name', 'Yasmine')->exists())->toBeFalse();
});

test('store rejette un doublon matière + enseignant dans les inscriptions', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject->teachers()->attach($teacher);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [
            [
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'payment_type' => 'monthly',
            ],
            [
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'payment_type' => 'vip',
            ],
        ],
    ])->assertSessionHasErrors('enrollments');

    expect(Student::where('first_name', 'Yasmine')->exists())->toBeFalse();
});

test('store accepte deux matières différentes avec le même enseignant', function () {
    $user = User::factory()->create();
    $subject1 = primaireSubject();
    $subject2 = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject1->teachers()->attach($teacher);
    $subject2->teachers()->attach($teacher);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [
            [
                'subject_id' => $subject1->id,
                'teacher_id' => $teacher->id,
                'payment_type' => 'monthly',
            ],
            [
                'subject_id' => $subject2->id,
                'teacher_id' => $teacher->id,
                'payment_type' => 'monthly',
            ],
        ],
    ])->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect(Student::where('first_name', 'Yasmine')->first()->enrollments)->toHaveCount(2);
});

test('AJAX et store retournent le même enseignant pour un même niveau', function () {
    $user = User::factory()->create();
    $subject = primaireSubject();
    $teacher = cycleTeacher('Ahmed', 'Benali', 'PRI');
    $subject->teachers()->attach($teacher);

    $this->actingAs($user)->getJson(route('students.subjects.teachers', [
        'subject' => $subject->id,
        'level' => '1AP',
    ]))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $teacher->id);

    $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Yasmine',
        'last_name' => 'Haddad',
        'level' => '1AP',
        'enrollments' => [[
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'payment_type' => 'monthly',
        ]],
    ])->assertRedirect(route('students.index'))
        ->assertSessionHas('success');

    expect(Student::where('first_name', 'Yasmine')->exists())->toBeTrue();
});
