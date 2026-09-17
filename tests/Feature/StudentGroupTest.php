<?php

use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\Level;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

test('student can belong to multiple groups', function () {
    $student = Student::factory()->create(['level' => '1AS']);
    $group1 = Group::factory()->create(['level' => '1AS']);
    $group2 = Group::factory()->create(['level' => '1AS']);

    $student->groups()->attach([$group1->id, $group2->id]);

    expect($student->groups)->toHaveCount(2);
    expect($student->groups->pluck('id'))->toContain($group1->id, $group2->id);
});

test('group can contain multiple students', function () {
    $group = Group::factory()->create(['level' => '1AS']);
    $s1 = Student::factory()->create(['level' => '1AS']);
    $s2 = Student::factory()->create(['level' => '1AS']);

    $group->students()->attach([$s1->id, $s2->id]);

    expect($group->fresh()->students)->toHaveCount(2);
});

test('admin can create student with one group', function () {
    $user = User::factory()->create();
    $level = Level::create(['name' => 'Primaire', 'code' => 'PRI', 'active' => true]);
    $subject = Subject::factory()->create(['level' => '1AP', 'primaire' => true]);
    $teacher = Teacher::factory()->create();
    $teacher->levels()->attach($level);
    $subject->teachers()->attach($teacher);

    $schoolYear = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
        'is_current' => true,
    ]);

    $group = Group::factory()->create([
        'level' => '1AP',
        'mode' => 'normal',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $schoolYear->id,
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
        'first_name' => 'Zain',
        'last_name' => 'Boukhari',
        'level' => '1AP',
        'enrollments' => [
            ['subject_id' => $subject->id, 'group_id' => $group->id, 'payment_type' => 'monthly'],
        ],
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('students.index'));

    $student = Student::where('first_name', 'Zain')->first();
    expect($student->groups)->toHaveCount(1);
    expect($student->groups->first()->id)->toBe($group->id);
    expect($student->groups->first()->pivot->is_active)->toBe(1);
});

test('admin can create student with multiple groups', function () {
    $user = User::factory()->create();
    $level = Level::create(['name' => 'Primaire', 'code' => 'PRI', 'active' => true]);
    $subject1 = Subject::factory()->create(['level' => '1AP', 'primaire' => true]);
    $subject2 = Subject::factory()->create(['level' => '1AP', 'primaire' => true]);
    $teacher = Teacher::factory()->create();
    $teacher->levels()->attach($level);
    $subject1->teachers()->attach($teacher);
    $subject2->teachers()->attach($teacher);

    $schoolYear = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
        'is_current' => true,
    ]);

    $g1 = Group::factory()->create([
        'level' => '1AP',
        'mode' => 'normal',
        'subject_id' => $subject1->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $schoolYear->id,
    ]);
    $g2 = Group::factory()->create([
        'level' => '1AP',
        'mode' => 'normal',
        'subject_id' => $subject2->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $schoolYear->id,
    ]);

    GroupTariff::create(['group_id' => $g1->id, 'billing_type' => 'monthly', 'student_price' => 1500, 'teacher_share' => 900, 'academy_share' => 600, 'effective_from' => now()->toDateString()]);
    GroupTariff::create(['group_id' => $g2->id, 'billing_type' => 'monthly', 'student_price' => 1500, 'teacher_share' => 900, 'academy_share' => 600, 'effective_from' => now()->toDateString()]);

    $response = $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Zain',
        'last_name' => 'Boukhari',
        'level' => '1AP',
        'enrollments' => [
            ['subject_id' => $subject1->id, 'group_id' => $g1->id, 'payment_type' => 'monthly'],
            ['subject_id' => $subject2->id, 'group_id' => $g2->id, 'payment_type' => 'monthly'],
        ],
    ]);

    $response->assertSessionHasNoErrors();

    $student = Student::where('first_name', 'Zain')->first();
    expect($student->groups)->toHaveCount(2);
});

test('group must match student level', function () {
    $user = User::factory()->create();
    $level = Level::create(['name' => 'Primaire', 'code' => 'PRI', 'active' => true]);
    $subject = Subject::factory()->create(['level' => '1AP', 'primaire' => true]);
    $teacher = Teacher::factory()->create();
    $teacher->levels()->attach($level);
    $subject->teachers()->attach($teacher);

    $schoolYear = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
        'is_current' => true,
    ]);

    $wrongGroup = Group::factory()->create([
        'level' => '2AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $schoolYear->id,
    ]);

    GroupTariff::create(['group_id' => $wrongGroup->id, 'billing_type' => 'monthly', 'student_price' => 1500, 'teacher_share' => 900, 'academy_share' => 600, 'effective_from' => now()->toDateString()]);

    $response = $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Zain',
        'last_name' => 'Boukhari',
        'level' => '1AP',
        'enrollments' => [
            ['subject_id' => $subject->id, 'group_id' => $wrongGroup->id, 'payment_type' => 'monthly'],
        ],
    ]);

    $response->assertSessionHasErrors('enrollments.0.group_id');
});

test('inactive group cannot be newly selected', function () {
    $user = User::factory()->create();
    $level = Level::create(['name' => 'Primaire', 'code' => 'PRI', 'active' => true]);
    $subject = Subject::factory()->create(['level' => '1AP', 'primaire' => true]);
    $teacher = Teacher::factory()->create();
    $teacher->levels()->attach($level);
    $subject->teachers()->attach($teacher);

    $schoolYear = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
        'is_current' => true,
    ]);

    $inactiveGroup = Group::factory()->create([
        'level' => '1AP',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $schoolYear->id,
        'is_active' => false,
    ]);

    $response = $this->actingAs($user)->post(route('students.store'), [
        'first_name' => 'Zain',
        'last_name' => 'Boukhari',
        'level' => '1AP',
        'enrollments' => [
            ['subject_id' => $subject->id, 'group_id' => $inactiveGroup->id, 'payment_type' => 'monthly'],
        ],
    ]);

    $response->assertSessionHasErrors('enrollments.0.group_id');
});

test('duplicate student/group prevented by unique constraint', function () {
    $student = Student::factory()->create();
    $group = Group::factory()->create();

    $student->groups()->attach($group->id);

    // Attaching again should not fail (syncWithoutDetaching), but the unique constraint prevents duplicates
    $student->groups()->syncWithoutDetaching([$group->id => ['joined_at' => now(), 'is_active' => true]]);

    expect($student->groups)->toHaveCount(1);
});

test('existing groups displayed on edit page', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['level' => '1AS']);
    $group = Group::factory()->create(['level' => '1AS']);
    $student->groups()->attach($group->id, ['joined_at' => now(), 'is_active' => true]);

    $response = $this->actingAs($user)->get(route('students.edit', $student));
    $response->assertOk();
});

test('groups can be added during update', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['level' => '1AS']);

    $subject = Subject::factory()->create(['level' => '1AS', 'lycee' => true]);
    $teacher = Teacher::factory()->create();
    $level = Level::create(['name' => 'Secondaire', 'code' => 'SEC', 'active' => true]);
    $teacher->levels()->attach($level);
    $subject->teachers()->attach($teacher);

    $g1 = Group::factory()->create([
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'is_active' => true,
    ]);

    $enrollment = $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'start_date' => now(),
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->put(route('students.update', $student), [
        'first_name' => $student->first_name,
        'last_name' => $student->last_name,
        'level' => '1AS',
        'enrollments' => [
            [
                'id' => $enrollment->id,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'group_id' => $g1->id,
            ],
        ],
    ]);

    $response->assertSessionHasNoErrors();

    expect($student->fresh()->groups)->toHaveCount(1);
});

test('groups are preserved when enrollments are submitted without group_id', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['level' => '1AS']);

    $subject = Subject::factory()->create(['level' => '1AS', 'lycee' => true]);
    $teacher = Teacher::factory()->create();
    $level = Level::create(['name' => 'Secondaire', 'code' => 'SEC', 'active' => true]);
    $teacher->levels()->attach($level);
    $subject->teachers()->attach($teacher);

    $g1 = Group::factory()->create([
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'is_active' => true,
    ]);

    $student->groups()->attach($g1->id, ['joined_at' => now(), 'is_active' => true]);

    $enrollment = $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'start_date' => now(),
        'status' => 'active',
    ]);

    // Update without group_id (simulates disabled select) → should preserve groups
    $response = $this->actingAs($user)->put(route('students.update', $student), [
        'first_name' => $student->first_name,
        'last_name' => $student->last_name,
        'level' => '1AS',
        'enrollments' => [
            [
                'id' => $enrollment->id,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
            ],
        ],
    ]);

    $response->assertSessionHasNoErrors();

    $pivot = $student->groups()->where('groups.id', $g1->id)->first();
    expect((int) $pivot->pivot->is_active)->toBe(1);
});

test('groups can be deactivated when group_id is explicitly set to null', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['level' => '1AS']);

    $subject = Subject::factory()->create(['level' => '1AS', 'lycee' => true]);
    $teacher = Teacher::factory()->create();
    $level = Level::create(['name' => 'Secondaire', 'code' => 'SEC', 'active' => true]);
    $teacher->levels()->attach($level);
    $subject->teachers()->attach($teacher);

    $g1 = Group::factory()->create([
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'is_active' => true,
    ]);

    $student->groups()->attach($g1->id, ['joined_at' => now(), 'is_active' => true]);

    $enrollment = $student->enrollments()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'start_date' => now(),
        'status' => 'active',
    ]);

    // Explicitly set group_id to null → should deactivate
    $response = $this->actingAs($user)->put(route('students.update', $student), [
        'first_name' => $student->first_name,
        'last_name' => $student->last_name,
        'level' => '1AS',
        'enrollments' => [
            [
                'id' => $enrollment->id,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'group_id' => null,
            ],
        ],
    ]);

    $response->assertSessionHasNoErrors();

    $pivot = $student->groups()->where('groups.id', $g1->id)->first();
    expect((int) $pivot->pivot->is_active)->toBe(0);
});

test('unauthenticated user rejected from student group routes', function () {
    $student = Student::factory()->create();
    $this->get(route('students.edit', $student))->assertRedirect('/login');
});

test('non-admin rejected from student group routes', function () {
    $student = Student::factory()->create();
    $studentUser = User::factory()->create(['role' => 'student']);
    $this->actingAs($studentUser)->get(route('students.edit', $student))->assertForbidden();
});

test('groups by level endpoint returns correct groups', function () {
    $user = User::factory()->create();
    $schoolYear = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
        'is_current' => true,
    ]);

    $g1 = Group::factory()->create(['level' => '1AS', 'school_year_id' => $schoolYear->id, 'is_active' => true]);
    $g2 = Group::factory()->create(['level' => '2AS', 'school_year_id' => $schoolYear->id, 'is_active' => true]);
    $g3 = Group::factory()->create(['level' => '1AS', 'school_year_id' => $schoolYear->id, 'is_active' => false]);

    $response = $this->actingAs($user)->getJson(route('groups.by-level', '1AS'));
    $response->assertOk();

    $data = $response->json();
    expect($data)->toHaveCount(1);
    expect($data[0]['id'])->toBe($g1->id);
});
