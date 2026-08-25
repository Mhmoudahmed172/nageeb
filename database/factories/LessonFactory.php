<?php

namespace Database\Factories;

use App\Enums\LessonContentType;
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
            'content_type' => LessonContentType::ExternalLink,
            'video_path' => null,
            'external_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'order_index' => 1,
        ];
    }
}
