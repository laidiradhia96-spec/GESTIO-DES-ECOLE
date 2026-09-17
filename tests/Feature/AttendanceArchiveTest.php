<?php

use App\Models\Attendance;
use App\Models\Group;
use App\Models\GroupTariff;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

// =========================================================
// HELPERS
// =========================================================

function archiveSchoolYear(): SchoolYear
{
    return SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]
    );
}

function archiveGroup(array $studentIds, int $teacherId, int $subjectId, string $groupName = 'Groupe Test'): Group
{
    $year = archiveSchoolYear();

    $group = Group::create([
        'name' => $groupName,
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
    }

    return $group;
}

function archiveAttendance(Student $student, Subject $subject, Group $group, string $date, string $status = 'present'): Attendance
{
    $year = archiveSchoolYear();

    return Attendance::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $group->teacher_id,
        'group_id' => $group->id,
        'date' => $date,
        'status' => $status,
        'school_year_id' => $year->id,
    ]);
}

// =========================================================
// TESTS
// =========================================================

test('la page archive charge correctement', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('attendances.archive'));

    $response->assertOk()
        ->assertSee('Archive des présences')
        ->assertSee('Rechercher un élève');
});

test('la recherche par nom retourne les élèves correspondants', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();

    $ahmed = Student::factory()->create([
        'first_name' => 'Ahmed',
        'last_name' => 'Ben Ali',
    ]);
    $other = Student::factory()->create([
        'first_name' => 'Sami',
        'last_name' => 'Bouzid',
    ]);

    $group = archiveGroup([$ahmed->id, $other->id], $teacher->id, $subject->id);

    $response = $this->actingAs($user)->get(route('attendances.archive', ['search' => 'Ahmed']));

    $response->assertOk()
        ->assertSee('Ahmed')
        ->assertSee('Ben Ali')
        ->assertDontSee('Bouzid');
});

test('plusieurs élèves correspondent à la recherche', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();

    $a1 = Student::factory()->create(['first_name' => 'Ahmed', 'last_name' => 'Ali']);
    $a2 = Student::factory()->create(['first_name' => 'Ahmed', 'last_name' => 'Mohamed']);
    $a3 = Student::factory()->create(['first_name' => 'Sami', 'last_name' => 'Bensalem']);

    $group = archiveGroup([$a1->id, $a2->id, $a3->id], $teacher->id, $subject->id);

    $response = $this->actingAs($user)->get(route('attendances.archive', ['search' => 'Ahmed']));

    $response->assertOk()
        ->assertSee('Ahmed')
        ->assertSee('Ali')
        ->assertSee('Mohamed')
        ->assertDontSee('Bensalem');
});

test('sélectionner un élève affiche son historique de présence', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create(['first_name' => 'Ahmed', 'last_name' => 'Ali']);

    $group = archiveGroup([$student->id], $teacher->id, $subject->id);

    archiveAttendance($student, $subject, $group, '2026-09-05', 'present');
    archiveAttendance($student, $subject, $group, '2026-09-07', 'absent');
    archiveAttendance($student, $subject, $group, '2026-09-12', 'present');

    $response = $this->actingAs($user)->get(route('attendances.archive', ['student_id' => $student->id]));

    $response->assertOk()
        ->assertSee('Ahmed')
        ->assertSee('Ali')
        ->assertSee('05/09/2026')
        ->assertSee('07/09/2026')
        ->assertSee('12/09/2026')
        ->assertSee('3 enregistrement(s)');
});

test('le filtre statut retourne uniquement les présences correspondantes', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create();

    $group = archiveGroup([$student->id], $teacher->id, $subject->id);

    archiveAttendance($student, $subject, $group, '2026-09-05', 'present');
    archiveAttendance($student, $subject, $group, '2026-09-07', 'absent');
    archiveAttendance($student, $subject, $group, '2026-09-12', 'present');

    $response = $this->actingAs($user)->get(route('attendances.archive', [
        'student_id' => $student->id,
        'status' => 'present',
    ]));

    $response->assertOk()
        ->assertSee('2 enregistrement(s)')
        ->assertSee('05/09/2026')
        ->assertSee('12/09/2026')
        ->assertDontSee('07/09/2026');
});

test('le filtre groupe retourne uniquement les présences de ce groupe', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create();

    $g1 = archiveGroup([$student->id], $teacher->id, $subject->id, 'Groupe G1');
    $g2 = archiveGroup([$student->id], $teacher->id, $subject->id, 'Groupe G2');

    archiveAttendance($student, $subject, $g1, '2026-09-05', 'present');
    archiveAttendance($student, $subject, $g2, '2026-09-07', 'absent');

    $response = $this->actingAs($user)->get(route('attendances.archive', [
        'student_id' => $student->id,
        'group_id' => $g1->id,
    ]));

    $response->assertOk()
        ->assertSee('1 enregistrement(s)')
        ->assertSee('05/09/2026')
        ->assertDontSee('07/09/2026');
});

test('le filtre date fonctionne correctement', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create();

    $group = archiveGroup([$student->id], $teacher->id, $subject->id);

    archiveAttendance($student, $subject, $group, '2026-09-05', 'present');
    archiveAttendance($student, $subject, $group, '2026-09-15', 'absent');
    archiveAttendance($student, $subject, $group, '2026-09-25', 'present');

    $response = $this->actingAs($user)->get(route('attendances.archive', [
        'student_id' => $student->id,
        'date_from' => '2026-09-10',
        'date_to' => '2026-09-20',
    ]));

    $response->assertOk()
        ->assertSee('1 enregistrement(s)')
        ->assertSee('15/09/2026')
        ->assertDontSee('05/09/2026')
        ->assertDontSee('25/09/2026');
});

test('le filtre année scolaire isole les données', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create();

    $year1 = SchoolYear::firstOrCreate(
        ['name' => '2025-2026'],
        ['start_date' => '2025-09-01', 'end_date' => '2026-08-31']
    );
    $year2 = SchoolYear::firstOrCreate(
        ['name' => '2026-2027'],
        ['start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'is_current' => true]
    );

    $g1 = Group::create([
        'name' => 'Groupe Y1',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year1->id,
        'max_students' => 30,
        'mode' => 'normal',
        'is_active' => true,
    ]);
    $g1->students()->attach($student->id, ['joined_at' => now(), 'is_active' => 1]);

    $g2 = Group::create([
        'name' => 'Groupe Y2',
        'level' => '1AS',
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year2->id,
        'max_students' => 30,
        'mode' => 'normal',
        'is_active' => true,
    ]);
    $g2->students()->attach($student->id, ['joined_at' => now(), 'is_active' => 1]);

    Attendance::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $g1->id,
        'date' => '2026-03-10',
        'status' => 'present',
        'school_year_id' => $year1->id,
    ]);

    Attendance::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'group_id' => $g2->id,
        'date' => '2026-09-10',
        'status' => 'absent',
        'school_year_id' => $year2->id,
    ]);

    // Year 2025-2026
    $this->actingAs($user)->get(route('attendances.archive', [
        'student_id' => $student->id,
        'school_year_id' => $year1->id,
    ]))
        ->assertOk()
        ->assertSee('1 enregistrement(s)')
        ->assertSee('10/03/2026')
        ->assertDontSee('10/09/2026');

    // Year 2026-2027
    $this->actingAs($user)->get(route('attendances.archive', [
        'student_id' => $student->id,
        'school_year_id' => $year2->id,
    ]))
        ->assertOk()
        ->assertSee('1 enregistrement(s)')
        ->assertSee('10/09/2026')
        ->assertDontSee('10/03/2026');
});

test('un élève dans plusieurs groupes a chaque enregistrement séparé', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create();

    $g1 = archiveGroup([$student->id], $teacher->id, $subject->id, 'Math G1');
    $g2 = archiveGroup([$student->id], $teacher->id, $subject->id, 'Physique G2');

    archiveAttendance($student, $subject, $g1, '2026-09-05', 'present');
    archiveAttendance($student, $subject, $g2, '2026-09-05', 'absent');

    $response = $this->actingAs($user)->get(route('attendances.archive', ['student_id' => $student->id]));

    $response->assertOk()
        ->assertSee('2 enregistrement(s)')
        ->assertSee('Math G1')
        ->assertSee('Physique G2');
});

test('le paramètre student_id ouvre directement l\'historique', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create(['first_name' => 'Ahmed', 'last_name' => 'Ali']);

    $group = archiveGroup([$student->id], $teacher->id, $subject->id);

    archiveAttendance($student, $subject, $group, '2026-09-05', 'present');

    $response = $this->actingAs($user)->get('/attendances/archive?student_id='.$student->id);

    $response->assertOk()
        ->assertSee('Ahmed')
        ->assertSee('Ali')
        ->assertSee('05/09/2026');
});

test('la pagination conserve les filtres', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();
    $student = Student::factory()->create();

    $group = archiveGroup([$student->id], $teacher->id, $subject->id);

    // Créer 30 présences pour déclencher la pagination
    for ($i = 1; $i <= 30; $i++) {
        archiveAttendance($student, $subject, $group, '2026-09-'.str_pad($i, 2, '0', STR_PAD_LEFT), 'present');
    }

    $response = $this->actingAs($user)->get(route('attendances.archive', [
        'student_id' => $student->id,
        'status' => 'present',
    ]));

    $response->assertOk()
        ->assertSee('30 enregistrement(s)');

    // Vérifier que la pagination contient les bons query params
    $response->assertSee('status=present');
});

test('aucun résultat affiche un message propre', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();

    $response = $this->actingAs($user)->get(route('attendances.archive', ['student_id' => $student->id]));

    $response->assertOk()
        ->assertSee('Aucune présence trouvée');
});

test('état initial affiche le message de recherche', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('attendances.archive'));

    $response->assertOk()
        ->assertSee('Recherchez un élève')
        ->assertSee('Archive des présences');
});
