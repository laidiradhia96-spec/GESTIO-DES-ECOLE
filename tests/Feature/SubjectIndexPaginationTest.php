<?php

use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

/**
 * Crée $count matières avec des created_at distincts (ordre déterministe
 * pour latest()) et des noms zéro-paddés uniques.
 */
function subjectPaginationSubjects(int $count, array $overrides = []): array
{
    $subjects = [];

    for ($i = 1; $i <= $count; $i++) {

        $subject = Subject::factory()->create(array_merge($overrides, [
            'name' => 'MATHEMATIQUE '.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
        ]));

        $subject->forceFill([
            'created_at' => now()->subMinutes($count - $i),
        ])->save();

        $subjects[] = $subject;
    }

    return $subjects;
}

function subjectPaginationYears(): array
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

function subjectPaginationEnrollment(Subject $subject, SchoolYear $year): Enrollment
{
    return Enrollment::factory()->create([
        'student_id' => Student::factory(),
        'subject_id' => $subject->id,
        'teacher_id' => Teacher::factory(),
        'status' => 'active',
        'school_year_id' => $year->id,
    ]);
}

test('16 matières → la page 1 affiche exactement 15 matières', function () {
    $user = User::factory()->create();

    $subjects = subjectPaginationSubjects(16);

    $newest = array_slice($subjects, 1); // 15 plus récentes
    $oldest = $subjects[0];

    $response = $this->actingAs($user)->get(route('subjects.index'));

    $response->assertOk();

    $response->assertViewHas('subjects', fn ($paginator) => $paginator->count() === 15);

    foreach ($newest as $subject) {
        $response->assertSee($subject->name);
    }

    $response->assertDontSee($oldest->name);
});

test('la page 2 contient la 16e matière', function () {
    $user = User::factory()->create();

    $subjects = subjectPaginationSubjects(16);

    $oldest = $subjects[0];
    $newest = array_slice($subjects, 1);

    $response = $this->actingAs($user)->get(route('subjects.index', ['page' => 2]));

    $response->assertOk();

    $response->assertViewHas('subjects', fn ($paginator) => $paginator->count() === 1);

    $response->assertSee($oldest->name);

    foreach ($newest as $subject) {
        $response->assertDontSee($subject->name);
    }
});

test('une matière de la page 2 n\'apparaît jamais sur la page 1', function () {
    $user = User::factory()->create();

    $subjects = subjectPaginationSubjects(16);

    $oldest = $subjects[0];

    $pageOne = $this->actingAs($user)->get(route('subjects.index'));

    $pageTwo = $this->actingAs($user)->get(route('subjects.index', ['page' => 2]));

    $pageOne->assertOk()
        ->assertDontSee($oldest->name);

    $pageTwo->assertOk()
        ->assertSee($oldest->name);
});

test('la recherche fonctionne avec la pagination', function () {
    $user = User::factory()->create();

    $subjects = subjectPaginationSubjects(16);

    Subject::factory()->create(['name' => 'HISTOIRE']);

    $oldest = $subjects[0];

    $pageOne = $this->actingAs($user)->get(route('subjects.index', ['search' => 'MATHEMATIQUE']));

    $pageTwo = $this->actingAs($user)->get(route('subjects.index', ['search' => 'MATHEMATIQUE', 'page' => 2]));

    $pageOne->assertOk()
        ->assertViewHas('subjects', fn ($paginator) => $paginator->count() === 15)
        ->assertDontSee('HISTOIRE');

    $pageTwo->assertOk()
        ->assertViewHas('subjects', fn ($paginator) => $paginator->count() === 1)
        ->assertSee($oldest->name)
        ->assertDontSee('HISTOIRE');
});

test('le filtre school_year_id fonctionne avec la pagination', function () {
    [$year2025, $year2026] = subjectPaginationYears();

    $user = User::factory()->create();

    $subjects = subjectPaginationSubjects(16);

    $oldest = $subjects[0];

    $otherYear = Subject::factory()->create(['name' => 'HISTOIRE']);

    foreach ($subjects as $subject) {
        subjectPaginationEnrollment($subject, $year2026);
    }

    subjectPaginationEnrollment($otherYear, $year2025);

    $pageOne = $this->actingAs($user)->get(route('subjects.index', ['school_year_id' => $year2026->id]));

    $pageTwo = $this->actingAs($user)->get(route('subjects.index', ['school_year_id' => $year2026->id, 'page' => 2]));

    $pageOne->assertOk()
        ->assertViewHas('subjects', fn ($paginator) => $paginator->count() === 15)
        ->assertDontSee('HISTOIRE');

    $pageTwo->assertOk()
        ->assertViewHas('subjects', fn ($paginator) => $paginator->count() === 1)
        ->assertSee($oldest->name)
        ->assertDontSee('HISTOIRE');
});

test('recherche + filtre + pagination conservent les paramètres (withQueryString)', function () {
    [$year2025, $year2026] = subjectPaginationYears();

    $user = User::factory()->create();

    $subjects = subjectPaginationSubjects(16);

    $oldest = $subjects[0];

    foreach ($subjects as $subject) {
        subjectPaginationEnrollment($subject, $year2026);
    }

    $response = $this->actingAs($user)->get(route('subjects.index', [
        'search' => 'MATHEMATIQUE',
        'school_year_id' => $year2026->id,
        'page' => 2,
    ]));

    $response->assertOk()
        ->assertViewHas('subjects', fn ($paginator) => $paginator->count() === 1)
        ->assertSee($oldest->name)
        ->assertSee('search=MATHEMATIQUE', false)
        ->assertSee('school_year_id='.$year2026->id, false)
        ->assertSee('page=1', false);
});

test('les matières existantes ne sont ni modifiées ni supprimées', function () {
    $user = User::factory()->create();

    $subjects = subjectPaginationSubjects(16);

    $snapshot = collect($subjects)->mapWithKeys(
        fn ($subject) => [$subject->id => $subject->name]
    )->all();

    $this->actingAs($user)->get(route('subjects.index'))->assertOk();
    $this->actingAs($user)->get(route('subjects.index', ['page' => 2]))->assertOk();
    $this->actingAs($user)->get(route('subjects.index', ['search' => 'MATHEMATIQUE']))->assertOk();

    expect(Subject::count())->toBe(16);

    foreach ($snapshot as $id => $name) {
        expect(Subject::find($id))->not->toBeNull()
            ->and(Subject::find($id)->name)->toBe($name);
    }
});
