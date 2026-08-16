<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Cycles fixes du système : jamais supprimés, jamais modifiés par l'admin.
     */
    public function run(): void
    {
        Level::firstOrCreate(
            ['code' => 'PRI'],
            ['name' => 'Primaire', 'active' => true]
        );

        Level::firstOrCreate(
            ['code' => 'MOY'],
            ['name' => 'Moyen', 'active' => true]
        );

        Level::firstOrCreate(
            ['code' => 'SEC'],
            ['name' => 'Lycée', 'active' => true]
        );
    }
}
