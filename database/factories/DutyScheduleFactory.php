<?php

namespace Database\Factories;

use App\Models\DutySchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DutySchedule>
 */
class DutyScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Random future duty date within the next 30 days
        $dutyDate = fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d');

        // Pick a plausible shift start: 06:00, 08:00, 14:00, or 20:00
        $startHour = fake()->randomElement([6, 8, 14, 20]);
        $shiftLength = fake()->randomElement([8, 12]);

        $timeStart = sprintf('%02d:00:00', $startHour);
        $timeEnd = sprintf('%02d:00:00', ($startHour + $shiftLength) % 24);

        return [
            'user_id' => User::factory(),
            'duty_date' => $dutyDate,
            'time_start' => $timeStart,
            'time_end' => $timeEnd,
            'created_by' => User::factory()->bfpAdmin(),
        ];
    }
}
