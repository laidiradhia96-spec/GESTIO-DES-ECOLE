<?php

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

function adminFilterSchoolYears(): array
{
    $year2025 = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
        'is_current' => true,
    ]);

    $year2026 = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-08-31',
    ]);

    return [$year2025, $year2026];
}

function adminFilterEnrollment(Student $student, Subject $subject, Teacher $teacher, SchoolYear $year): Enrollment
{
    return Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'school_year_id' => $year->id,
    ]);
}

test('les élèves sont filtrés par année scolaire via leurs inscriptions', function () {
    [$year2025, $year2026] = adminFilterSchoolYears();

    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    $current = Student::factory()->create(['last_name' => 'Courant']);
    $other = Student::factory()->create(['last_name' => 'Autre']);
    $unlinked = Student::factory()->create(['last_name' => 'SansInscription']);

    adminFilterEnrollment($current, $subject, $teacher, $year2025);
    adminFilterEnrollment($other, $subject, $teacher, $year2026);

    // Défaut : année courante
    $this->actingAs($user)->get(route('students.index'))
        ->assertOk()
        ->assertSee('Courant')
        ->assertDontSee('Autre')
        ->assertDontSee('SansInscription');

    // Année explicite
    $this->actingAs($user)->get(route('students.index', ['school_year_id' => $year2026->id]))
        ->assertOk()
        ->assertSee('Autre')
        ->assertDontSee('Courant');

    // Toutes les années : tout, y compris les élèves sans inscription
    $this->actingAs($user)->get(route('students.index', ['school_year_id' => '']))
        ->assertOk()
        ->assertSee('Courant')
        ->assertSee('Autre')
        ->assertSee('SansInscription');
});

test('les enseignants sont filtrés par année scolaire via inscriptions ou séances', function () {
    [$year2025, $year2026] = adminFilterSchoolYears();

    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();

    $byEnrollment = Teacher::factory()->create(['last_name' => 'Inscrite']);
    $bySession = Teacher::factory()->create(['last_name' => 'Seance']);
    $unlinked = Teacher::factory()->create(['last_name' => 'SansAffectation']);

    adminFilterEnrollment($student, $subject, $byEnrollment, $year2025);

    ClassSession::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $bySession->id,
        'day' => 'Lundi',
        'start_time' => '09:00',
        'end_time' => '10:00',
        'start_date' => '2027-01-10',
        'status' => 'active',
        'school_year_id' => $year2026->id,
    ]);

    $this->actingAs($user)->get(route('teachers.index'))
        ->assertOk()
        ->assertSee('Inscrite')
        ->assertDontSee('Seance')
        ->assertDontSee('SansAffectation');

    $this->actingAs($user)->get(route('teachers.index', ['school_year_id' => $year2026->id]))
        ->assertOk()
        ->assertSee('Seance')
        ->assertDontSee('Inscrite');

    $this->actingAs($user)->get(route('teachers.index', ['school_year_id' => '']))
        ->assertOk()
        ->assertSee('Inscrite')
        ->assertSee('Seance')
        ->assertSee('SansAffectation');
});

test('les matières sont filtrées par année scolaire via inscriptions ou séances', function () {
    [$year2025, $year2026] = adminFilterSchoolYears();

    $user = User::factory()->create();
    $student = Student::factory()->create();
    $teacher = Teacher::factory()->create();

    $byEnrollment = Subject::factory()->create(['name' => 'MATIMATIQUE']);
    $bySession = Subject::factory()->create(['name' => 'ENGLES']);
    $unlinked = Subject::factory()->create(['name' => 'HISTOIRE']);

    adminFilterEnrollment($student, $byEnrollment, $teacher, $year2025);

    ClassSession::create([
        'student_id' => $student->id,
        'subject_id' => $bySession->id,
        'teacher_id' => $teacher->id,
        'day' => 'Mardi',
        'start_time' => '09:00',
        'end_time' => '10:00',
        'start_date' => '2027-01-10',
        'status' => 'active',
        'school_year_id' => $year2026->id,
    ]);

    $this->actingAs($user)->get(route('subjects.index'))
        ->assertOk()
        ->assertSee('MATIMATIQUE')
        ->assertDontSee('ENGLES')
        ->assertDontSee('HISTOIRE');

    $this->actingAs($user)->get(route('subjects.index', ['school_year_id' => $year2026->id]))
        ->assertOk()
        ->assertSee('ENGLES')
        ->assertDontSee('MATIMATIQUE');

    $this->actingAs($user)->get(route('subjects.index', ['school_year_id' => '']))
        ->assertOk()
        ->assertSee('MATIMATIQUE')
        ->assertSee('ENGLES')
        ->assertSee('HISTOIRE');
});

test('les présences sont filtrées par année scolaire', function () {
    [$year2025, $year2026] = adminFilterSchoolYears();

    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => '2026-08-15',
        'status' => 'present',
        'school_year_id' => $year2025->id,
    ]);

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => '2027-01-15',
        'status' => 'present',
        'school_year_id' => $year2026->id,
    ]);

    $this->actingAs($user)->get(route('attendances.index'))
        ->assertOk()
        ->assertSee('15/08/2026')
        ->assertDontSee('15/01/2027');

    $this->actingAs($user)->get(route('attendances.index', ['school_year_id' => $year2026->id]))
        ->assertOk()
        ->assertSee('15/01/2027')
        ->assertDontSee('15/08/2026');

    $this->actingAs($user)->get(route('attendances.index', ['school_year_id' => '']))
        ->assertOk()
        ->assertSee('15/08/2026')
        ->assertSee('15/01/2027');
});

test('les paiements filtrent par année scolaire avec statistiques scopées', function () {
    [$year2025, $year2026] = adminFilterSchoolYears();

    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();

    Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'period' => '2026-08',
        'amount_due' => 1500,
        'amount_paid' => 1500,
        'remaining_amount' => 0,
        'payment_date' => '2026-08-10',
        'school_year_id' => $year2025->id,
    ]);

    Payment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'period' => '2027-01',
        'amount_due' => 2000,
        'amount_paid' => 500,
        'remaining_amount' => 1500,
        'payment_date' => '2027-01-10',
        'school_year_id' => $year2026->id,
    ]);

    // Défaut : Toutes les années
    $this->actingAs($user)->get(route('payments.index'))
        ->assertOk()
        ->assertSee('2026-08')
        ->assertSee('2027-01')
        ->assertViewHas('totalPaid', 2000.0)
        ->assertViewHas('totalRemaining', 1500.0);

    // Année explicite : liste + statistiques scopées
    $this->actingAs($user)->get(route('payments.index', ['school_year_id' => $year2025->id]))
        ->assertOk()
        ->assertSee('2026-08')
        ->assertDontSee('2027-01')
        ->assertViewHas('totalPaid', 1500.0)
        ->assertViewHas('totalRemaining', 0.0);
});

test('les lignes sans année scolaire n\'apparaissent que dans Toutes les années', function () {
    [$year2025] = adminFilterSchoolYears();

    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => '2026-08-15',
        'status' => 'present',
    ]);

    // Défaut : année courante → la présence non liée est masquée
    $this->actingAs($user)->get(route('attendances.index'))
        ->assertOk()
        ->assertDontSee('15/08/2026');

    // Toutes les années → elle réapparaît
    $this->actingAs($user)->get(route('attendances.index', ['school_year_id' => '']))
        ->assertOk()
        ->assertSee('15/08/2026');
});
