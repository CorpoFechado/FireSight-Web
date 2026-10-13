<?php

namespace Database\Factories;

use App\Enums\DutyGroup;
use App\Models\DutyOverride;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DutyOverride>
 */
class DutyOverrideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->bfpPersonnel(),
            'duty_group' => fake()->randomElement(DutyGroup::cases()),
            'starts_on' => now()->startOfWeek(CarbonInterface::MONDAY)->toDateString(),
            'ends_on' => now()->endOfWeek(CarbonInterface::SUNDAY)->toDateString(),
            'reason' => fake()->sentence(),
            'created_by' => User::factory()->bfpAdmin(),
        ];
    }
}
