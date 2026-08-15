<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'receipt_number' => fake()->unique()->numerify('REC-2026-#####'),
            'student_id' => Student::factory(),
            'subject_id' => Subject::factory(),
            'payment_type' => fake()->randomElement(['monthly', 'vip']),
            'period' => fake()->date('Y-m'),
            'amount_due' => fake()->randomFloat(2, 500, 10000),
            'amount_paid' => 0,
            'remaining_amount' => 0,
            'payment_method' => fake()->randomElement(['espèces', 'chèque', 'virement']),
            'payment_date' => fake()->date(),
            'payment_time' => fake()->time('H:i:s'),
            'note' => null,
        ];
    }
}
