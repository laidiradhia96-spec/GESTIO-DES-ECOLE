<?php

use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

function studentSubjectsHistory(): array
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

test('l\'élève connecté ne voit que ses propres matières', function () {
    [$user, $student] = studentSubjectsHistory();

    $teacher = Teacher::factory()->create(['last_name' => 'MonProf']);

    $mySubject = Subject::factory()->create(['name' => 'MATH']);
    $otherSubject = Subject::factory()->create(['name' => 'ANGLAIS']);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $mySubject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
    ]);

    $other = Student::factory()->create();
    Enrollment::factory()->create([
        'student_id' => $other->id,
        'subject_id' => $otherSubject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
    ]);

    $this->actingAs($user)->get(route('student.subjects'))
        ->assertOk()
        ->assertSee('MATH')
        ->assertDontSee('ANGLAIS');
});

test('les matières de l\'élève sont filtrées par année scolaire', function () {
    [$user, $student, $year2025, $year2026] = studentSubjectsHistory();

    $teacher = Teacher::factory()->create();

    $subject2025 = Subject::factory()->create(['name' => 'MATIMATIQUE']);
    $subject2026 = Subject::factory()->create(['name' => 'ENGLES']);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject2025->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'school_year_id' => $year2025->id,
    ]);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject2026->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'school_year_id' => $year2026->id,
    ]);

    // Défaut : toutes les années → historique complet
    $this->actingAs($user)->get(route('student.subjects'))
        ->assertOk()
        ->assertSee('MATIMATIQUE')
        ->assertSee('ENGLES');

    // Année explicite
    $this->actingAs($user)->get(route('student.subjects', ['school_year_id' => $year2025->id]))
        ->assertOk()
        ->assertSee('MATIMATIQUE')
        ->assertDontSee('ENGLES');

    // Autre année explicite
    $this->actingAs($user)->get(route('student.subjects', ['school_year_id' => $year2026->id]))
        ->assertOk()
        ->assertDontSee('MATIMATIQUE')
        ->assertSee('ENGLES');
});

test('l\'historique affiche aussi les inscriptions non actives', function () {
    [$user, $student] = studentSubjectsHistory();

    $teacher = Teacher::factory()->create();

    $activeSubject = Subject::factory()->create(['name' => 'MATIMATIQUE']);
    $inactiveSubject = Subject::factory()->create(['name' => 'HISTOIRE']);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $activeSubject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
    ]);

    Enrollment::factory()->inactive()->create([
        'student_id' => $student->id,
        'subject_id' => $inactiveSubject->id,
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($user)->get(route('student.subjects'))
        ->assertOk()
        ->assertSee('MATIMATIQUE')
        ->assertSee('HISTOIRE')
        ->assertSee('Ancienne');
});

test('le niveau affiché correspond au niveau de l\'élève', function () {
    $year = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id, 'level' => '2AM']);

    $teacher = Teacher::factory()->create();

    $subject = Subject::factory()->create(['name' => 'ENGLES', 'level' => 'all', 'moyen' => true]);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year->id,
    ]);

    $this->actingAs($user)->get(route('student.subjects'))
        ->assertOk()
        ->assertSee('2AM')
        ->assertDontSee('Moyen')
        ->assertDontSee('all');
});

test('une inscription sans année scolaire apparaît par défaut', function () {
    [$user, $student] = studentSubjectsHistory();

    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['name' => 'SANSANNEE']);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($user)->get(route('student.subjects'))
        ->assertOk()
        ->assertSee('SANSANNEE');
});

test('l\'historique des matières est paginé', function () {
    [$user, $student] = studentSubjectsHistory();

    $teacher = Teacher::factory()->create();

    for ($i = 1; $i <= 16; $i++) {
        $subject = Subject::factory()->create(['name' => 'MATIERE'.$i]);

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ]);
    }

    $pageOne = $this->actingAs($user)->get(route('student.subjects'))
        ->assertOk();

    expect($pageOne->viewData('enrollments')->count())->toBe(15);

    $pageTwo = $this->actingAs($user)->get(route('student.subjects', ['page' => 2]))
        ->assertOk();

    expect($pageTwo->viewData('enrollments')->count())->toBe(1);
});

test('un compte sans profil élève reçoit une erreur 403', function () {
    $user = User::factory()->create(['role' => 'student']);

    $this->actingAs($user)->get(route('student.subjects'))
        ->assertForbidden();
});
