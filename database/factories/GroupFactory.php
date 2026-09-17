<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'subject_id' => Subject::factory(),
            'level' => fake()->randomElement([
                '1AP', '2AP', '3AP', '4AP', '5AP',
                '1AM', '2AM', '3AM', '4AM',
                '1AS', '2AS', '3AS',
            ]),
            'school_year_id' => SchoolYear::firstOrCreate(
                ['name' => '2026-2027'],
                [
                    'start_date' => '2026-09-01',
                    'end_date' => '2027-06-30',
                    'is_current' => true,
                ]
            ),
            'name' => 'Groupe '.fake()->numberBetween(1, 5),
            'mode' => fake()->randomElement(['normal', 'special', 'vip']),
            'is_active' => true,
        ];
    }
}
