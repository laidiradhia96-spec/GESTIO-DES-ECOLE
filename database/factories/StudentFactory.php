<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'date_of_birth' => fake()->date(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'level' => fake()->randomElement(['1AP', '2AP', '3AP', '4AP', '5AP', '1AM', '2AM', '3AM', '4AM', '1AS', '2AS', '3AS']),
            'parent_name' => fake()->name(),
            'parent_phone' => fake()->phoneNumber(),
        ];
    }
}
