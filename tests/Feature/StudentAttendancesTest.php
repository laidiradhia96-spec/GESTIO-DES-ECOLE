<?php

use App\Models\Attendance;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

function studentAttendanceHistory(): array
{
    $year2025 = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    $year2026 = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-08-31',
    ]);

    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);

    return [$user, $student, $year2025, $year2026];
}

test('l\'élève connecté ne voit que ses propres présences', function () {
    [$user, $student] = studentAttendanceHistory();

    $subject = Subject::factory()->create(['name' => 'MATH']);
    $teacher = Teacher::factory()->create(['last_name' => 'MonProf']);

    $other = Student::factory()->create();
    $otherSubject = Subject::factory()->create(['name' => 'ANGLAIS']);
    $otherTeacher = Teacher::factory()->create(['last_name' => 'ProfAutre']);

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => '2026-08-15',
        'status' => 'present',
    ]);

    Attendance::factory()->create([
        'student_id' => $other->id,
        'subject_id' => $otherSubject->id,
        'teacher_id' => $otherTeacher->id,
        'date' => '2027-01-15',
        'status' => 'absent',
    ]);

    $this->actingAs($user)->get(route('student.attendances'))
        ->assertOk()
        ->assertSee('15/08/2026')
        ->assertSee('MATH')
        ->assertSee('MonProf')
        ->assertDontSee('15/01/2027')
        ->assertDontSee('ANGLAIS')
        ->assertDontSee('ProfAutre');
});

test('les présences de l\'élève sont filtrées par année scolaire', function () {
    [$user, $student, $year2025, $year2026] = studentAttendanceHistory();

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

    // Défaut : toutes les années → historique complet
    $this->actingAs($user)->get(route('student.attendances'))
        ->assertOk()
        ->assertSee('15/08/2026')
        ->assertSee('15/01/2027');

    // Année explicite
    $this->actingAs($user)->get(route('student.attendances', ['school_year_id' => $year2025->id]))
        ->assertOk()
        ->assertSee('15/08/2026')
        ->assertDontSee('15/01/2027');

    // Autre année explicite
    $this->actingAs($user)->get(route('student.attendances', ['school_year_id' => $year2026->id]))
        ->assertOk()
        ->assertDontSee('15/08/2026')
        ->assertSee('15/01/2027');
});

test('les présences de l\'élève sont filtrées par date', function () {
    [$user, $student] = studentAttendanceHistory();

    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => '2026-08-15',
        'status' => 'present',
    ]);

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => '2026-08-20',
        'status' => 'absent',
    ]);

    $this->actingAs($user)->get(route('student.attendances', ['date' => '2026-08-15']))
        ->assertOk()
        ->assertSee('15/08/2026')
        ->assertDontSee('20/08/2026');
});

test('les présences de l\'élève sont filtrées par enseignant', function () {
    [$user, $student] = studentAttendanceHistory();

    $subject = Subject::factory()->create();
    $firstTeacher = Teacher::factory()->create(['last_name' => 'FiltreProf']);
    $secondTeacher = Teacher::factory()->create(['last_name' => 'AutreProf']);

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $firstTeacher->id,
        'date' => '2026-08-15',
        'status' => 'present',
    ]);

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $secondTeacher->id,
        'date' => '2026-08-20',
        'status' => 'present',
    ]);

    $this->actingAs($user)->get(route('student.attendances', ['teacher_id' => $firstTeacher->id]))
        ->assertOk()
        ->assertSee('15/08/2026')
        ->assertDontSee('20/08/2026');
});

test('la liste des enseignants du filtre est limitée aux présences de l\'élève', function () {
    [$user, $student] = studentAttendanceHistory();

    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create(['last_name' => 'MonFiltre']);
    $otherTeacher = Teacher::factory()->create(['last_name' => 'SansPresence']);

    Attendance::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => '2026-08-15',
        'status' => 'present',
    ]);

    $this->actingAs($user)->get(route('student.attendances'))
        ->assertOk()
        ->assertSee('MonFiltre')
        ->assertDontSee('SansPresence');
});

test('l\'historique des présences est paginé', function () {
    [$user, $student] = studentAttendanceHistory();

    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();

    for ($day = 1; $day <= 16; $day++) {
        Attendance::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'date' => '2026-08-'.str_pad($day, 2, '0', STR_PAD_LEFT),
            'status' => 'present',
        ]);
    }

    $pageOne = $this->actingAs($user)->get(route('student.attendances'))
        ->assertOk();

    expect($pageOne->viewData('attendances')->count())->toBe(15);

    $pageTwo = $this->actingAs($user)->get(route('student.attendances', ['page' => 2]))
        ->assertOk();

    expect($pageTwo->viewData('attendances')->count())->toBe(1);
});

test('un compte sans profil élève reçoit une erreur 403', function () {
    $user = User::factory()->create(['role' => 'student']);

    $this->actingAs($user)->get(route('student.attendances'))
        ->assertForbidden();
});
