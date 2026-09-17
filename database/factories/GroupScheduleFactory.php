<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\GroupSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupScheduleFactory extends Factory
{
    protected $model = GroupSchedule::class;

    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 17);
        $startMinute = fake()->randomElement(['00', '30']);
        $endHour = $startHour + fake()->numberBetween(1, 2);
        $endMinute = $startMinute === '00' ? '30' : '00';

        if ($endHour > 19) {
            $endHour = 19;
        }

        return [
            'group_id' => Group::factory(),
            'day' => fake()->randomElement([
                'Dimanche', 'Lundi', 'Mardi', 'Mercredi',
                'Jeudi', 'Vendredi', 'Samedi',
            ]),
            'start_time' => sprintf('%02d:%s', $startHour, $startMinute),
            'end_time' => sprintf('%02d:%s', $endHour, $endMinute),
            'room' => fake()->optional(0.3)->randomElement([
                'Salle 01', 'Salle 02', 'Salle 03', 'Bâtiment A',
            ]),
            'is_active' => true,
        ];
    }
}
