<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use Illuminate\Database\Seeder;

class SchoolYearSeeder extends Seeder
{
    /**
     * Années de démarrage du système.
     *
     * Ce ne sont PAS une limite : l'administrateur pourra créer librement
     * de nouvelles années (2028-2029, 2029-2030, ...) sans modification de code.
     */
    public function run(): void
    {
        $years = [
            ['2025-2026', '2025-09-01', '2026-08-31'],
            ['2026-2027', '2026-09-01', '2027-08-31'],
            ['2027-2028', '2027-09-01', '2028-08-31'],
        ];

        foreach ($years as [$name, $startDate, $endDate]) {

            SchoolYear::firstOrCreate(
                ['name' => $name],
                [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]
            );
        }

        // Déterminer automatiquement l'année courante uniquement si
        // aucune n'est déjà définie (un choix admin n'est jamais écrasé).
        if (SchoolYear::where('is_current', true)->doesntExist()) {

            $current = SchoolYear::forDate(now());

            if ($current) {

                SchoolYear::setCurrent($current);
            }
        }
    }
}
