<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\LessonContentType;
use App\Models\Lesson;
use App\Models\LessonContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonContent>
 */
class LessonContentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'type' => LessonContentType::Text,
            'title' => null,
            'position' => 1,
            'data' => ['body' => fake()->paragraph()],
            'status' => ContentStatus::Live,
        ];
    }
}
