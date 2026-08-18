<?php

use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

function studentSubjectsJourney(): array
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
    $student = Student::factory()->create(['user_id' => $user->id, 'level' => '2AM']);

    return [$user, $student, $year2025, $year2026];
}

test('parcours complet : l\'élève accède à ses matières et à lui seul', function () {
    [$user, $student, $year2025, $year2026] = studentSubjectsJourney();

    $teacher = Teacher::factory()->create(['last_name' => 'ProfVisible']);

    $math = Subject::factory()->create(['name' => 'MATIMATIQUE']);
    $physique = Subject::factory()->create(['name' => 'PHYSIC']);
    $sansAnnee = Subject::factory()->create(['name' => 'SANSANNEE']);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $math->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'school_year_id' => $year2025->id,
    ]);

    Enrollment::factory()->inactive()->create([
        'student_id' => $student->id,
        'subject_id' => $physique->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year2026->id,
    ]);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $sansAnnee->id,
        'teacher_id' => $teacher->id,
    ]);

    $other = Student::factory()->create(['level' => '4AM']);
    $otherTeacher = Teacher::factory()->create(['last_name' => 'ProfCache']);
    $otherSubject = Subject::factory()->create(['name' => 'ANGLAIS']);

    Enrollment::factory()->create([
        'student_id' => $other->id,
        'subject_id' => $otherSubject->id,
        'teacher_id' => $otherTeacher->id,
        'status' => 'active',
        'school_year_id' => $year2025->id,
    ]);

    $this->actingAs($user)->get(route('student.subjects'))
        ->assertOk()
        // En-têtes du tableau
        ->assertSee('Matière')
        ->assertSee('Enseignant')
        ->assertSee('Niveau')
        ->assertSee('Année scolaire')
        ->assertSee('Statut')
        // Niveau = niveau réel de l'élève
        ->assertSee('2AM')
        ->assertDontSee('4AM')
        // Ses propres matières, enseignants et années
        ->assertSee('MATIMATIQUE')
        ->assertSee('PHYSIC')
        ->assertSee('SANSANNEE')
        ->assertSee('ProfVisible')
        ->assertSee('2025-2026')
        ->assertSee('2026-2027')
        // Historique : active + inactive + sans année
        ->assertSee('✅ Active')
        ->assertSee('⏳ Ancienne')
        // Défaut : Toutes les années
        ->assertSee('Toutes les années')
        // Jamais les matières d'un autre élève
        ->assertDontSee('ANGLAIS')
        ->assertDontSee('ProfCache');
});

test('le filtre année scolaire restreint l\'historique de l\'élève', function () {
    [$user, $student, $year2025, $year2026] = studentSubjectsJourney();

    $teacher = Teacher::factory()->create();

    $math = Subject::factory()->create(['name' => 'MATIMATIQUE']);
    $physique = Subject::factory()->create(['name' => 'PHYSIC']);
    $sansAnnee = Subject::factory()->create(['name' => 'SANSANNEE']);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $math->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year2025->id,
    ]);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $physique->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year2026->id,
    ]);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $sansAnnee->id,
        'teacher_id' => $teacher->id,
    ]);

    // Année 2025-2026 uniquement
    $this->actingAs($user)->get(route('student.subjects', ['school_year_id' => $year2025->id]))
        ->assertOk()
        ->assertSee('MATIMATIQUE')
        ->assertDontSee('PHYSIC')
        ->assertDontSee('SANSANNEE');

    // Année 2026-2027 uniquement
    $this->actingAs($user)->get(route('student.subjects', ['school_year_id' => $year2026->id]))
        ->assertOk()
        ->assertDontSee('MATIMATIQUE')
        ->assertSee('PHYSIC')
        ->assertDontSee('SANSANNEE');

    // Sans filtre : Toutes les années, y compris sans année scolaire
    $this->actingAs($user)->get(route('student.subjects'))
        ->assertOk()
        ->assertSee('MATIMATIQUE')
        ->assertSee('PHYSIC')
        ->assertSee('SANSANNEE');
});

test('l\'historique des matières de l\'élève est paginé', function () {
    [$user, $student] = studentSubjectsJourney();

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

test('un compte élève sans profil élève reçoit 403', function () {
    $user = User::factory()->create(['role' => 'student']);

    $this->actingAs($user)->get(route('student.subjects'))
        ->assertForbidden();
});

test('un admin ne peut pas utiliser la page comme espace élève', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Student::factory()->create(['user_id' => $admin->id, 'level' => '2AM']);

    $this->actingAs($admin)->get(route('student.subjects'))
        ->assertForbidden();

    $this->actingAs($admin)->get(route('student.attendances'))
        ->assertForbidden();
});

test('un admin sans profil élève reçoit 403', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('student.subjects'))
        ->assertForbidden();
});
