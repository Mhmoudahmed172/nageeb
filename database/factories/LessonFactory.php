<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Lesson;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unit_id' => Unit::factory(),
            'title' => fake()->sentence(3),
            'description' => null,
            'position' => 1,
            'status' => ContentStatus::Live,
            'is_preview' => false,
            'estimated_duration' => 15,
        ];
    }
}
