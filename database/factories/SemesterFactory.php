<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Course;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Semester>
 */
class SemesterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => 'الفصل الأول',
            'description' => null,
            'position' => 1,
            'status' => ContentStatus::Live,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Semester $semester): void {
            if (! $semester->course_id) {
                return;
            }

            $semester->position = (int) Semester::query()
                ->where('course_id', $semester->course_id)
                ->max('position') + 1;
        });
    }
}
