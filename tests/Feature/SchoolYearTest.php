<?php

use App\Models\SchoolYear;
use Carbon\Carbon;
use Database\Seeders\SchoolYearSeeder;

test('le seeder crée les trois années de démarrage', function () {
    $this->seed(SchoolYearSeeder::class);

    expect(SchoolYear::count())->toBe(3)

        ->and(SchoolYear::where('name', '2025-2026')->value('start_date')->toDateString())->toBe('2025-09-01')
        ->and(SchoolYear::where('name', '2025-2026')->value('end_date')->toDateString())->toBe('2026-08-31')

        ->and(SchoolYear::where('name', '2026-2027')->value('start_date')->toDateString())->toBe('2026-09-01')
        ->and(SchoolYear::where('name', '2026-2027')->value('end_date')->toDateString())->toBe('2027-08-31')

        ->and(SchoolYear::where('name', '2027-2028')->value('start_date')->toDateString())->toBe('2027-09-01')
        ->and(SchoolYear::where('name', '2027-2028')->value('end_date')->toDateString())->toBe('2028-08-31');
});

test('relancer le seeder ne crée pas de doublons', function () {
    $this->seed(SchoolYearSeeder::class);
    $this->seed(SchoolYearSeeder::class);

    expect(SchoolYear::count())->toBe(3);
});

test('le seeder ne modifie pas une année existante', function () {
    SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-01-01',
        'end_date' => '2025-12-31',
    ]);

    $this->seed(SchoolYearSeeder::class);

    expect(SchoolYear::where('name', '2025-2026')->value('start_date')->toDateString())->toBe('2025-01-01')
        ->and(SchoolYear::where('name', '2025-2026')->value('end_date')->toDateString())->toBe('2025-12-31')
        ->and(SchoolYear::count())->toBe(3);
});

test('le seeder marque is_current sur l\'année contenant la date actuelle', function () {
    $this->seed(SchoolYearSeeder::class);

    $current = SchoolYear::current();

    expect(SchoolYear::where('is_current', true)->count())->toBe(1)
        ->and($current)->not->toBeNull()
        ->and($current->start_date->lte(now()))->toBeTrue()
        ->and($current->end_date->gte(now()))->toBeTrue();
});

test('le seeder ne réécrase pas un is_current déjà défini', function () {
    $this->seed(SchoolYearSeeder::class);

    $adminChoice = SchoolYear::where('name', '2026-2027')->first();

    SchoolYear::setCurrent($adminChoice);

    $this->seed(SchoolYearSeeder::class);

    expect(SchoolYear::where('is_current', true)->count())->toBe(1)
        ->and(SchoolYear::current()->id)->toBe($adminChoice->id);
});

test('forDate retourne l\'année aux bornes incluses et null hors intervalle', function () {
    $year = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    expect(SchoolYear::forDate('2025-09-01')->id)->toBe($year->id)
        ->and(SchoolYear::forDate('2026-08-31')->id)->toBe($year->id)
        ->and(SchoolYear::forDate('2026-09-01'))->toBeNull()
        ->and(SchoolYear::forDate('2025-08-31'))->toBeNull();
});

test('current retourne l\'unique année courante ou null', function () {
    SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    expect(SchoolYear::current())->toBeNull();

    $year = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-08-31',
        'is_current' => true,
    ]);

    expect(SchoolYear::current()->id)->toBe($year->id);
});

test('les dates du modèle sont des instances de Carbon', function () {
    $year = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    expect($year->start_date)->toBeInstanceOf(Carbon::class)
        ->and($year->end_date)->toBeInstanceOf(Carbon::class)
        ->and($year->refresh()->is_current)->toBeFalse();
});

test('le système ne limite pas la création d\'années futures', function () {
    $this->seed(SchoolYearSeeder::class);

    $year2028 = SchoolYear::create([
        'name' => '2028-2029',
        'start_date' => '2028-09-01',
        'end_date' => '2029-08-31',
    ]);

    $year2029 = SchoolYear::create([
        'name' => '2029-2030',
        'start_date' => '2029-09-01',
        'end_date' => '2030-08-31',
    ]);

    expect(SchoolYear::count())->toBe(5)
        ->and(SchoolYear::forDate('2028-10-01')->id)->toBe($year2028->id)
        ->and(SchoolYear::forDate('2029-12-01')->id)->toBe($year2029->id);

    $this->seed(SchoolYearSeeder::class);

    expect(SchoolYear::count())->toBe(5)
        ->and(SchoolYear::where('name', '2028-2029')->value('start_date')->toDateString())->toBe('2028-09-01');
});

test('setCurrent conserve une seule année is_current = true', function () {
    $a = SchoolYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-08-31',
    ]);

    $b = SchoolYear::create([
        'name' => '2026-2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-08-31',
    ]);

    $c = SchoolYear::create([
        'name' => '2027-2028',
        'start_date' => '2027-09-01',
        'end_date' => '2028-08-31',
    ]);

    SchoolYear::setCurrent($a);

    expect(SchoolYear::where('is_current', true)->count())->toBe(1)
        ->and(SchoolYear::current()->id)->toBe($a->id);

    SchoolYear::setCurrent($b);

    expect(SchoolYear::where('is_current', true)->count())->toBe(1)
        ->and(SchoolYear::current()->id)->toBe($b->id)
        ->and($a->refresh()->is_current)->toBeFalse()
        ->and($c->refresh()->is_current)->toBeFalse();
});
