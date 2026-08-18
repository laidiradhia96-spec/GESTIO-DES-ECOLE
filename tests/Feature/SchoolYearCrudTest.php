<?php

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\PaymentSignalement;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\UnpaidSignalement;
use App\Models\User;

function crudSchoolYears(): array
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

test('un admin peut créer une année scolaire', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('school-years.store'), [
            'name' => '2028-2029',
            'start_date' => '2028-09-01',
            'end_date' => '2029-08-31',
        ])
        ->assertRedirect(route('school-years.show', 1));

    expect(SchoolYear::where('name', '2028-2029')->exists())->toBeTrue();
});

test('la première année créée devient automatiquement courante', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('school-years.store'), [
            'name' => '2028-2029',
            'start_date' => '2028-09-01',
            'end_date' => '2029-08-31',
        ])
        ->assertRedirect();

    expect(SchoolYear::where('is_current', true)->count())->toBe(1)
        ->and(SchoolYear::first()->is_current)->toBeTrue();
});

test('le nom d\'une année scolaire doit être unique', function () {
    crudSchoolYears();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('school-years.store'), [
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-08-31',
        ])
        ->assertSessionHasErrors('name');

    expect(SchoolYear::where('name', '2025-2026')->count())->toBe(1);
});

test('le nom d\'une année scolaire doit respecter le format AAAA-AAAA', function () {
    crudSchoolYears();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('school-years.store'), [
            'name' => '2025/2026',
            'start_date' => '2028-09-01',
            'end_date' => '2029-08-31',
        ])
        ->assertSessionHasErrors('name');
});

test('la date de fin doit être postérieure à la date de début', function () {
    crudSchoolYears();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('school-years.store'), [
            'name' => '2028-2029',
            'start_date' => '2028-09-01',
            'end_date' => '2028-09-01',
        ])
        ->assertSessionHasErrors('end_date');
});

test('un chevauchement d\'intervalles est refusé', function () {
    crudSchoolYears();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('school-years.store'), [
            'name' => '2029-2030',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ])
        ->assertSessionHasErrors('start_date');

    expect(SchoolYear::where('name', '2029-2030')->doesntExist())->toBeTrue();
});

test('deux années adjacentes sont acceptées', function () {
    crudSchoolYears();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('school-years.store'), [
            'name' => '2027-2028',
            'start_date' => '2027-09-01',
            'end_date' => '2028-08-31',
        ])
        ->assertRedirect();

    expect(SchoolYear::where('name', '2027-2028')->exists())->toBeTrue();
});

test('un admin peut modifier une année scolaire', function () {
    [$year2025] = crudSchoolYears();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('school-years.update', $year2025), [
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-08-31',
        ])
        ->assertRedirect(route('school-years.show', $year2025));

    expect($year2025->fresh()->start_date->toDateString())->toBe('2025-09-15');
});

test('une modification vers un intervalle chevauchant est refusée', function () {
    [$year2025, $year2026] = crudSchoolYears();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('school-years.update', $year2026), [
            'name' => '2026-2027',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ])
        ->assertSessionHasErrors('start_date');

    expect($year2026->fresh()->start_date->toDateString())->toBe('2026-09-01');
});

test('définir l\'année courante bascule le statut (une seule à la fois)', function () {
    [$year2025, $year2026] = crudSchoolYears();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('school-years.set-current', $year2026))
        ->assertRedirect();

    expect(SchoolYear::where('is_current', true)->count())->toBe(1)
        ->and(SchoolYear::current()->id)->toBe($year2026->id);

    $this->actingAs($user)
        ->post(route('school-years.set-current', $year2025))
        ->assertRedirect();

    expect(SchoolYear::where('is_current', true)->count())->toBe(1)
        ->and(SchoolYear::current()->id)->toBe($year2025->id);
});

test('une année contenant des données ne peut pas être supprimée', function () {
    [$year2025] = crudSchoolYears();
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();

    $records = [
        fn () => Attendance::factory()->create(['school_year_id' => $year2025->id]),
        fn () => Enrollment::factory()->create(['school_year_id' => $year2025->id]),
        fn () => Payment::factory()->create(['school_year_id' => $year2025->id]),
        fn () => ClassSession::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day' => 'Lundi',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'school_year_id' => $year2025->id,
        ]),
        fn () => PaymentSignalement::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'period' => now()->format('Y-m'),
            'amount_remaining' => 0,
            'signalement_date' => now()->toDateString(),
            'school_year_id' => $year2025->id,
        ]),
        fn () => UnpaidSignalement::create([
            'student_id' => $student->id,
            'period' => now()->format('Y-m'),
            'school_year_id' => $year2025->id,
        ]),
        fn () => PaymentSchedule::create([
            'student_id' => $student->id,
            'period' => now()->format('Y-m'),
            'amount_due' => 100,
            'school_year_id' => $year2025->id,
        ]),
    ];

    foreach ($records as $create) {
        $create();

        $this->actingAs($user)
            ->delete(route('school-years.destroy', $year2025))
            ->assertRedirect()
            ->assertSessionHas('error');

        expect(SchoolYear::find($year2025->id))->not->toBeNull();
    }
});

test('l\'année courante ne peut pas être supprimée même vide', function () {
    [$year2025, $year2026] = crudSchoolYears();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('school-years.destroy', $year2025))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(SchoolYear::find($year2025->id))->not->toBeNull()
        ->and(SchoolYear::count())->toBe(2);
});

test('une année vide non courante peut être supprimée', function () {
    [$year2025, $year2026] = crudSchoolYears();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('school-years.destroy', $year2026))
        ->assertRedirect(route('school-years.index'))
        ->assertSessionHas('success');

    expect(SchoolYear::find($year2026->id))->toBeNull()
        ->and(SchoolYear::find($year2025->id))->not->toBeNull();
});

test('defaultId retombe sur l\'année contenant aujourd\'hui sans année courante', function () {
    $year = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    expect(SchoolYear::defaultId())->toBe($year->id);
});

test('defaultId retombe sur la dernière année si aucune ne contient aujourd\'hui', function () {
    $year = SchoolYear::create([
        'name' => '2020-2021',
        'start_date' => '2020-09-01',
        'end_date' => '2021-08-31',
    ]);

    expect(SchoolYear::defaultId())->toBe($year->id);
});

test('defaultId retourne null sans aucune année', function () {
    expect(SchoolYear::defaultId())->toBeNull();
});

test('les filtres admin retombent sur l\'année contenant aujourd\'hui sans année courante', function () {
    $year = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    $user = User::factory()->create();
    $student = Student::factory()->create(['last_name' => 'AnneeActuelle']);
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create();

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'status' => 'active',
        'school_year_id' => $year->id,
    ]);

    $this->actingAs($user)
        ->get(route('students.index'))
        ->assertOk()
        ->assertSee('AnneeActuelle');
});

test('créer 2028-2029 ne modifie pas les données historiques', function () {
    [$year2025] = crudSchoolYears();
    $user = User::factory()->create();
    $student = Student::factory()->create();
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();

    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year_id' => $year2025->id,
    ]);

    $this->actingAs($user)
        ->post(route('school-years.store'), [
            'name' => '2028-2029',
            'start_date' => '2028-09-01',
            'end_date' => '2029-08-31',
        ])
        ->assertRedirect();

    $year2028 = SchoolYear::where('name', '2028-2029')->first();

    expect(SchoolYear::forDate('2028-10-01')->id)->toBe($year2028->id)
        ->and(SchoolYear::forPeriod('2028-10')->id)->toBe($year2028->id)
        ->and($enrollment->fresh()->school_year_id)->toBe($year2025->id);
});

test('l\'index liste les années avec l\'année courante mise en évidence', function () {
    [$year2025, $year2026] = crudSchoolYears();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('school-years.index'))
        ->assertOk()
        ->assertSee('2025-2026')
        ->assertSee('2026-2027')
        ->assertSee('Année courante');
});

test('la page de consultation affiche les compteurs de données', function () {
    [$year2025] = crudSchoolYears();
    $user = User::factory()->create();

    Enrollment::factory()->create(['school_year_id' => $year2025->id]);

    $this->actingAs($user)
        ->get(route('school-years.show', $year2025))
        ->assertOk()
        ->assertSee('Inscriptions')
        ->assertSee('2025-2026');
});

test('le formulaire de modification est accessible', function () {
    [$year2025] = crudSchoolYears();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('school-years.edit', $year2025))
        ->assertOk()
        ->assertSee('2025-2026');
});
