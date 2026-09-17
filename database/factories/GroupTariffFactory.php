<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\GroupTariff;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupTariffFactory extends Factory
{
    protected $model = GroupTariff::class;

    public function definition(): array
    {
        $studentPrice = fake()->randomElement([1500, 2000, 3000, 5000, 6000]);
        $teacherShare = (int) ($studentPrice * fake()->randomFloat(2, 0.5, 0.75));
        $academyShare = $studentPrice - $teacherShare;

        return [
            'group_id' => Group::factory(),
            'billing_type' => fake()->randomElement(['monthly', 'per_session']),
            'student_price' => $studentPrice,
            'teacher_share' => $teacherShare,
            'academy_share' => $academyShare,
            'effective_from' => fake()->date(),
            'effective_to' => null,
            'is_active' => true,
        ];
    }
}
