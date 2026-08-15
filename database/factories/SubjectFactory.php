<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'code' => fake()->unique()->lexify('???'),
            'description' => fake()->sentence(),
            'level' => fake()->randomElement(['1AP', '2AP', '3AP', '4AP', '5AP', '1AM', '2AM', '3AM', '4AM', '1AS', '2AS', '3AS']),
            'hours_per_week' => fake()->numberBetween(1, 6),
            'active' => true,
        ];
    }
}
