<?php

use App\Models\Level;
use Database\Seeders\LevelSeeder;

test('le seeder crée les trois cycles fixes PRI, MOY et SEC', function () {
    $this->seed(LevelSeeder::class);

    expect(Level::where('code', 'PRI')->value('name'))->toBe('Primaire')
        ->and(Level::where('code', 'MOY')->value('name'))->toBe('Moyen')
        ->and(Level::where('code', 'SEC')->value('name'))->toBe('Lycée')
        ->and(Level::where('code', 'PRI')->value('active'))->toBeTrue()
        ->and(Level::where('code', 'MOY')->value('active'))->toBeTrue()
        ->and(Level::where('code', 'SEC')->value('active'))->toBeTrue()
        ->and(Level::count())->toBe(3);
});

test('relancer le seeder ne crée pas de doublons', function () {
    $this->seed(LevelSeeder::class);
    $this->seed(LevelSeeder::class);

    expect(Level::count())->toBe(3);
});

test('le seeder ne modifie pas un niveau existant', function () {
    Level::create([
        'name' => 'Niveau existant',
        'code' => 'PRI',
        'active' => false,
    ]);

    $this->seed(LevelSeeder::class);

    expect(Level::where('code', 'PRI')->value('name'))->toBe('Niveau existant')
        ->and(Level::where('code', 'PRI')->value('active'))->toBeFalse()
        ->and(Level::count())->toBe(3);
});