<?php

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\PaymentSignalement;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\PaymentSignalementService;

function activeEnrollment(Student $student, Subject $subject, Teacher $teacher): Enrollment
{
    return Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'payment_type' => 'monthly',
    ]);
}

test('un utilisateur authentifié peut supprimer une présence', function () {
    $user = User::factory()->create();
    $attendance = Attendance::factory()->create([
        'student_id' => Student::factory()->create()->id,
        'subject_id' => Subject::factory()->create()->id,
        'teacher_id' => Teacher::factory()->create()->id,
        'date' => '2026-08-10',
        'status' => 'present',
    ]);

    $response = $this->actingAs($user)->delete(route('attendances.destroy', $attendance));

    $response->assertRedirect(route('attendances.index'))
        ->assertSessionHas('success');

    expect(Attendance::find($attendance->id))->toBeNull();
});

test('la suppression conserve les filtres de la liste', function () {
    $user = User::factory()->create();
    $attendance = Attendance::factory()->create([
        'student_id' => Student::factory()->create()->id,
        'subject_id' => Subject::factory()->create()->id,
        'teacher_id' => Teacher::factory()->create()->id,
        'date' => '2026-08-10',
        'status' => 'present',
    ]);

    $response = $this->actingAs($user)->delete(route('attendances.destroy', $attendance).'?status=present&date=2026-08-10');

    $response->assertRedirect(route('attendances.index', [
        'status' => 'present',
        'date' => '2026-08-10',
    ]));
});

test('supprimer une présence inexistante renvoie une 404', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->delete(route('attendances.destroy', 9999))
        ->assertNotFound();
});

test('l\'endpoint students-by-group ne renvoie que les membres actifs du groupe', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher1 = Teacher::factory()->create();
    $teacher2 = Teacher::factory()->create();

    $enrolled = Student::factory()->create();
    $group = createGroupWithStudents([$enrolled->id], $teacher1->id, $subject->id);

    $otherGroupStudent = Student::factory()->create();
    $otherGroup = createGroupWithStudents([$otherGroupStudent->id], $teacher2->id, $subject->id);

    $notEnrolled = Student::factory()->create();

    $response = $this->actingAs($user)->getJson(route('attendances.students-by-group', [
        'group_id' => $group->id,
    ]));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['id' => $enrolled->id])
        ->assertJsonMissing(['id' => $otherGroupStudent->id])
        ->assertJsonMissing(['id' => $notEnrolled->id]);
});

test('l\'endpoint students exige matière et enseignant', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    $this->actingAs($user)->getJson(route('attendances.students-by-group'))
        ->assertUnprocessable();

    $this->actingAs($user)->getJson(route('attendances.students-by-group', [
        'group_id' => 999,
    ]))->assertUnprocessable();
});

test('la page de création affiche le formulaire enseignant et groupe', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create([
        'first_name' => 'NomUniqueTest',
    ]);

    $response = $this->actingAs($user)->get(route('attendances.create'));

    $response->assertOk()
        ->assertSee('Enseignant')
        ->assertSee('Groupe')
        ->assertDontSee($student->first_name);
});

test('store rejette un élève non inscrit à ce groupe', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create();
    $notEnrolled = Student::factory()->create();

    $group = createGroupWithStudents([$student->id], $teacher->id, $subject->id);

    $response = $this->actingAs($user)->from(route('attendances.create'))->post(route('attendances.store'), [
        'date' => '2026-09-15',
        'group_id' => $group->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'students' => [
            ['id' => $notEnrolled->id, 'status' => 'present'],
        ],
    ]);

    $response->assertSessionHasErrors('students.0.id');

    expect(Attendance::count())->toBe(0);
});

test('store exige un groupe', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();

    $this->actingAs($user)->post(route('attendances.store'), [
        'date' => '2026-09-15',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'students' => [
            ['id' => Student::factory()->create()->id, 'status' => 'present'],
        ],
    ])->assertSessionHasErrors('group_id');

    expect(Attendance::count())->toBe(0);
});

test('store enregistre la présence d\'un élève inscrit au groupe', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create();

    $year = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    $group = createGroupWithStudents([$student->id], $teacher->id, $subject->id);

    $response = $this->actingAs($user)->post(route('attendances.store'), [
        'date' => '2026-08-15',
        'group_id' => $group->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'students' => [
            ['id' => $student->id, 'status' => 'present', 'note' => 'ok'],
        ],
    ]);

    $response->assertRedirect(route('attendances.index'))
        ->assertSessionHas('success');

    $attendance = Attendance::first();

    expect($attendance)->not->toBeNull()
        ->and($attendance->student_id)->toBe($student->id)
        ->and($attendance->subject_id)->toBe($subject->id)
        ->and($attendance->teacher_id)->toBe($teacher->id)
        ->and($attendance->group_id)->toBe($group->id)
        ->and($attendance->status)->toBe('present')
        ->and($attendance->note)->toBe('ok')
        ->and($attendance->school_year_id)->toBe($year->id);
});

test('update rejette l\'ajout d\'un élève non inscrit au groupe', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $enrolled = Student::factory()->create();
    $notEnrolled = Student::factory()->create();

    $group = createGroupWithStudents([$enrolled->id], $teacher->id, $subject->id);

    $attendance = Attendance::factory()->create([
        'student_id' => $enrolled->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'date' => '2026-08-10',
        'status' => 'present',
    ]);

    $response = $this->actingAs($user)->put(route('attendances.update', $attendance), [
        'date' => '2026-08-10',
        'group_id' => $group->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'students' => [
            ['id' => $enrolled->id, 'status' => 'absent'],
            ['id' => $notEnrolled->id, 'status' => 'present'],
        ],
    ]);

    $response->assertSessionHasErrors('students.1.id');

    expect(Attendance::where('student_id', $notEnrolled->id)->count())->toBe(0);
});

test('supprimer la seule présence du mois supprime le signalement ouvert', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create();
    activeEnrollment($student, $subject, $teacher);

    $attendance = Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => '2026-08-10',
        'status' => 'present',
    ]);

    app(PaymentSignalementService::class)->syncFromAttendance($attendance);

    expect(PaymentSignalement::where('student_id', $student->id)
        ->where('subject_id', $subject->id)
        ->where('period', '2026-08')
        ->where('status', 'pending')
        ->count())->toBe(1);

    $this->actingAs($user)->delete(route('attendances.destroy', $attendance))
        ->assertRedirect(route('attendances.index'));

    expect(PaymentSignalement::where('student_id', $student->id)
        ->where('subject_id', $subject->id)
        ->where('period', '2026-08')
        ->where('status', 'pending')
        ->count())->toBe(0);
});

test('supprimer une présence conserve le signalement si une autre présence reste dans le mois', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create();
    activeEnrollment($student, $subject, $teacher);

    $service = app(PaymentSignalementService::class);

    $first = Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => '2026-08-10',
        'status' => 'present',
    ]);

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => '2026-08-20',
        'status' => 'late',
    ]);

    $service->syncFromAttendance($first);

    expect(PaymentSignalement::where('period', '2026-08')
        ->where('status', 'pending')
        ->count())->toBe(1);

    $this->actingAs($user)->delete(route('attendances.destroy', $first));

    expect(PaymentSignalement::where('period', '2026-08')
        ->where('status', 'pending')
        ->count())->toBe(1);
});

test('supprimer la seule présence VIP du jour supprime le signalement du jour', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create();

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'payment_type' => 'vip',
    ]);

    $attendance = Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => '2026-08-05',
        'status' => 'present',
    ]);

    app(PaymentSignalementService::class)->syncFromAttendance($attendance);

    expect(PaymentSignalement::where('period', '2026-08-05')
        ->where('status', 'pending')
        ->count())->toBe(1);

    $this->actingAs($user)->delete(route('attendances.destroy', $attendance));

    expect(PaymentSignalement::where('period', '2026-08-05')
        ->where('status', 'pending')
        ->count())->toBe(0);
});

// =========================================================
// GROUP-BASED ATTENDANCE TESTS
// =========================================================

function createGroupWithStudents(array $studentIds, int $teacherId, int $subjectId, string $name = 'Groupe Test'): Group
{
    $year = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30']
    );

    $group = Group::create([
        'name' => $name,
        'level' => '1AS',
        'subject_id' => $subjectId,
        'teacher_id' => $teacherId,
        'school_year_id' => $year->id,
        'max_students' => 30,
        'mode' => 'normal',
        'is_active' => true,
    ]);

    GroupTariff::create([
        'group_id' => $group->id,
        'billing_type' => 'monthly',
        'student_price' => 3000,
        'teacher_share' => 2000,
        'academy_share' => 1000,
        'effective_from' => now()->toDateString(),
    ]);

    foreach ($studentIds as $studentId) {
        $group->students()->attach($studentId, [
            'joined_at' => now(),
            'is_active' => 1,
        ]);

        Enrollment::create([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'teacher_id' => $teacherId,
            'group_id' => $group->id,
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'payment_type' => 'monthly',
            'school_year_id' => $year->id,
        ]);
    }

    return $group;
}

test('store with group_id creates attendance linked to group', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $student = Student::factory()->create();

    $group = createGroupWithStudents([$student->id], $teacher->id, $subject->id);

    $response = $this->actingAs($user)->post(route('attendances.store'), [
        'date' => '2026-09-15',
        'group_id' => $group->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'students' => [
            ['id' => $student->id, 'status' => 'present', 'note' => 'bon'],
        ],
    ]);

    $response->assertRedirect(route('attendances.index'))
        ->assertSessionHas('success');

    $attendance = Attendance::first();
    expect($attendance)->not->toBeNull()
        ->and($attendance->group_id)->toBe($group->id)
        ->and($attendance->student_id)->toBe($student->id)
        ->and($attendance->subject_id)->toBe($subject->id);
});

test('store rejects student not in group', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $studentInGroup = Student::factory()->create();
    $studentOutside = Student::factory()->create();

    $group = createGroupWithStudents([$studentInGroup->id], $teacher->id, $subject->id);

    $response = $this->actingAs($user)->from(route('attendances.create'))->post(route('attendances.store'), [
        'date' => '2026-09-15',
        'group_id' => $group->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'students' => [
            ['id' => $studentOutside->id, 'status' => 'present'],
        ],
    ]);

    $response->assertSessionHasErrors('students.0.id');
    expect(Attendance::count())->toBe(0);
});

test('store requires group_id', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $student = Student::factory()->create();

    $this->actingAs($user)->post(route('attendances.store'), [
        'date' => '2026-09-15',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'students' => [
            ['id' => $student->id, 'status' => 'present'],
        ],
    ])->assertSessionHasErrors('group_id');

    expect(Attendance::count())->toBe(0);
});

test('groupsByTeacher endpoint returns groups for teacher', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $student = Student::factory()->create();

    $group = createGroupWithStudents([$student->id], $teacher->id, $subject->id);

    $response = $this->actingAs($user)->getJson(route('attendances.groups-by-teacher', [
        'teacher_id' => $teacher->id,
    ]));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['id' => $group->id]);
});

test('studentsByGroup endpoint returns students in group', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $student = Student::factory()->create();
    $outside = Student::factory()->create();

    $group = createGroupWithStudents([$student->id], $teacher->id, $subject->id);

    $response = $this->actingAs($user)->getJson(route('attendances.students-by-group', [
        'group_id' => $group->id,
    ]));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['id' => $student->id])
        ->assertJsonMissing(['id' => $outside->id]);
});

test('create page returns only teachers', function () {
    $user = User::factory()->create();
    Teacher::factory()->create(['last_name' => 'Dupont']);

    $response = $this->actingAs($user)->get(route('attendances.create'));

    $response->assertOk()
        ->assertSee('Dupont');
});

test('show page loads group relationship', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $student = Student::factory()->create();

    $group = createGroupWithStudents([$student->id], $teacher->id, $subject->id);

    $attendance = Attendance::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'date' => '2026-09-15',
        'status' => 'present',
    ]);

    $response = $this->actingAs($user)->get(route('attendances.show', $attendance));

    $response->assertOk()
        ->assertSee($group->name);
});

test('edit page shows group as read-only', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $student = Student::factory()->create();

    $group = createGroupWithStudents([$student->id], $teacher->id, $subject->id);

    $attendance = Attendance::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'date' => '2026-09-15',
        'status' => 'present',
    ]);

    $response = $this->actingAs($user)->get(route('attendances.edit', $attendance));

    $response->assertOk()
        ->assertSee($group->name)
        ->assertSee('group_id');
});

test('index page shows group column', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();
    $student = Student::factory()->create();

    $group = createGroupWithStudents([$student->id], $teacher->id, $subject->id);

    Attendance::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'date' => '2026-09-15',
        'status' => 'present',
    ]);

    $response = $this->actingAs($user)->get(route('attendances.index'));

    $response->assertOk()
        ->assertSee($group->name)
        ->assertSee('Groupe');
});

// =====================================================
// TESTS : GROUPING BY SESSION
// =====================================================

test('multiple students same session show as one row', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();
    $student3 = Student::factory()->create();

    $group = createGroupWithStudents([$student1->id, $student2->id, $student3->id], $teacher->id, $subject->id);

    $year = SchoolYear::where('name', '2026-2027')->first();
    $date = '2026-09-14';

    foreach ([$student1, $student2, $student3] as $student) {
        Attendance::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'date' => $date,
            'status' => 'present',
            'school_year_id' => $year->id,
        ]);
    }

    $response = $this->actingAs($user)->get(route('attendances.index'));

    $response->assertOk()
        ->assertSeeText('1 séances')
        ->assertSeeInOrder(['5', 'élèves'])
        ->assertSee($teacher->first_name)
        ->assertSee($subject->name)
        ->assertSee($group->name);
});

test('two different groups create two session rows', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();

    $group1 = createGroupWithStudents([$student1->id], $teacher->id, $subject->id, 'Groupe A');
    $group2 = createGroupWithStudents([$student2->id], $teacher->id, $subject->id, 'Groupe B');

    $year = SchoolYear::where('name', '2026-2027')->first();
    $date = '2026-09-14';

    Attendance::create([
        'student_id' => $student1->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group1->id,
        'date' => $date,
        'status' => 'present',
        'school_year_id' => $year->id,
    ]);

    Attendance::create([
        'student_id' => $student2->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group2->id,
        'date' => $date,
        'status' => 'absent',
        'school_year_id' => $year->id,
    ]);

    $response = $this->actingAs($user)->get(route('attendances.index'));

    $response->assertOk()
        ->assertSee('2 séances')
        ->assertSee($group1->name)
        ->assertSee($group2->name);
});

test('two different dates create two session rows', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $student = Student::factory()->create();
    $group = createGroupWithStudents([$student->id], $teacher->id, $subject->id);

    $year = SchoolYear::where('name', '2026-2027')->first();

    Attendance::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'date' => '2026-09-14',
        'status' => 'present',
        'school_year_id' => $year->id,
    ]);

    Attendance::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'date' => '2026-09-15',
        'status' => 'absent',
        'school_year_id' => $year->id,
    ]);

    $response = $this->actingAs($user)->get(route('attendances.index'));

    $response->assertOk()
        ->assertSee('2 séances')
        ->assertSee('14/09/2026')
        ->assertSee('15/09/2026');
});

test('two different subjects create two session rows', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();

    $subject1 = Subject::factory()->create();
    $subject2 = Subject::factory()->create();

    $student = Student::factory()->create();
    $group1 = createGroupWithStudents([$student->id], $teacher->id, $subject1->id);
    $group2 = createGroupWithStudents([$student->id], $teacher->id, $subject2->id);

    $year = SchoolYear::where('name', '2026-2027')->first();
    $date = '2026-09-14';

    Attendance::create([
        'student_id' => $student->id,
        'subject_id' => $subject1->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group1->id,
        'date' => $date,
        'status' => 'present',
        'school_year_id' => $year->id,
    ]);

    Attendance::create([
        'student_id' => $student->id,
        'subject_id' => $subject2->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group2->id,
        'date' => $date,
        'status' => 'present',
        'school_year_id' => $year->id,
    ]);

    $response = $this->actingAs($user)->get(route('attendances.index'));

    $response->assertOk()
        ->assertSee('2 séances')
        ->assertSee($subject1->name)
        ->assertSee($subject2->name);
});

test('two different teachers create two session rows', function () {
    $user = User::factory()->create();

    $teacher1 = Teacher::factory()->create();
    $teacher2 = Teacher::factory()->create();

    $subject = Subject::factory()->create();

    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();

    $group1 = createGroupWithStudents([$student1->id], $teacher1->id, $subject->id);
    $group2 = createGroupWithStudents([$student2->id], $teacher2->id, $subject->id);

    $year = SchoolYear::where('name', '2026-2027')->first();
    $date = '2026-09-14';

    Attendance::create([
        'student_id' => $student1->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher1->id,
        'group_id' => $group1->id,
        'date' => $date,
        'status' => 'present',
        'school_year_id' => $year->id,
    ]);

    Attendance::create([
        'student_id' => $student2->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher2->id,
        'group_id' => $group2->id,
        'date' => $date,
        'status' => 'absent',
        'school_year_id' => $year->id,
    ]);

    $response = $this->actingAs($user)->get(route('attendances.index'));

    $response->assertOk()
        ->assertSee('2 séances')
        ->assertSee($teacher1->first_name)
        ->assertSee($teacher2->first_name);
});

test('student count is correct per session', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $students = Student::factory()->count(5)->create();
    $studentIds = $students->pluck('id')->toArray();
    $group = createGroupWithStudents($studentIds, $teacher->id, $subject->id);

    $year = SchoolYear::where('name', '2026-2027')->first();
    $date = '2026-09-14';

    foreach ($students as $student) {
        Attendance::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'date' => $date,
            'status' => 'present',
            'school_year_id' => $year->id,
        ]);
    }

    $response = $this->actingAs($user)->get(route('attendances.index'));

    $response->assertOk()
        ->assertSeeText('élèves')
        ->assertSeeText('1 séances')
        ->assertSeeInOrder(['5', 'élèves']);
});

test('session filter by teacher works', function () {
    $user = User::factory()->create();

    $teacher1 = Teacher::factory()->create();
    $teacher2 = Teacher::factory()->create();

    $subject = Subject::factory()->create();

    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();

    $group1 = createGroupWithStudents([$student1->id], $teacher1->id, $subject->id);
    $group2 = createGroupWithStudents([$student2->id], $teacher2->id, $subject->id);

    $year = SchoolYear::where('name', '2026-2027')->first();
    $date = '2026-09-14';

    Attendance::create([
        'student_id' => $student1->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher1->id,
        'group_id' => $group1->id,
        'date' => $date,
        'status' => 'present',
        'school_year_id' => $year->id,
    ]);

    Attendance::create([
        'student_id' => $student2->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher2->id,
        'group_id' => $group2->id,
        'date' => $date,
        'status' => 'present',
        'school_year_id' => $year->id,
    ]);

    $response = $this->actingAs($user)->get(route('attendances.index', [
        'teacher_id' => $teacher1->id,
    ]));

    $response->assertOk()
        ->assertSeeText('1 séances')
        ->assertSee($teacher1->first_name);
});

test('show page displays all students in session', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();
    $student3 = Student::factory()->create();

    $group = createGroupWithStudents([$student1->id, $student2->id, $student3->id], $teacher->id, $subject->id);

    $date = '2026-09-14';

    Attendance::create([
        'student_id' => $student1->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'date' => $date,
        'status' => 'present',
    ]);

    Attendance::create([
        'student_id' => $student2->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'date' => $date,
        'status' => 'absent',
    ]);

    Attendance::create([
        'student_id' => $student3->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'date' => $date,
        'status' => 'present',
    ]);

    $ref = Attendance::where('student_id', $student1->id)->first();

    $response = $this->actingAs($user)->get(route('attendances.show', $ref));

    $response->assertOk()
        ->assertSee($student1->first_name)
        ->assertSee($student2->first_name)
        ->assertSee($student3->first_name)
        ->assertSee('Présent')
        ->assertSee('Absent');
});

test('print page displays session details', function () {
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();

    $group = createGroupWithStudents([$student1->id, $student2->id], $teacher->id, $subject->id);

    $year = SchoolYear::where('name', '2026-2027')->first();
    $date = '2026-09-14';

    Attendance::create([
        'student_id' => $student1->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'date' => $date,
        'status' => 'present',
        'school_year_id' => $year->id,
    ]);

    Attendance::create([
        'student_id' => $student2->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $group->id,
        'date' => $date,
        'status' => 'absent',
        'school_year_id' => $year->id,
    ]);

    $ref = Attendance::where('student_id', $student1->id)->first();

    $response = $this->actingAs($user)->get(route('attendances.print', $ref));

    $response->assertOk()
        ->assertSee($teacher->first_name)
        ->assertSee($subject->name)
        ->assertSee($group->name)
        ->assertSee($student1->first_name)
        ->assertSee($student2->first_name)
        ->assertSee('Feuille de présence');
});
