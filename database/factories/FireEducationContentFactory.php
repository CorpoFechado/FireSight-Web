<?php

namespace Database\Factories;

use App\Models\FireEducationContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FireEducationContent>
 */
class FireEducationContentFactory extends Factory
{
    protected $model = FireEducationContent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'category' => fake()->randomElement(['prevention', 'emergency_response', 'awareness']),
            'summary' => fake()->sentence(10),
            'body' => fake()->paragraphs(3, true),
            'image_path' => null,
            'read_minutes' => fake()->numberBetween(2, 15),
            'is_featured' => fake()->boolean(20),
            'created_at' => now(),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => [
            'is_featured' => true,
        ]);
    }
}
