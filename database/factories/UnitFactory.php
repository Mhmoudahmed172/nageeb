<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'semester_id' => Semester::factory(),
            'title' => 'الوحدة الأولى',
            'description' => null,
            'position' => 1,
            'status' => ContentStatus::Live,
        ];
    }

    public function forCourse(Course $course): static
    {
        return $this->state(fn () => [
            'semester_id' => $course->semesters()->first()?->id
                ?? $course->semesters()->create([
                    'title' => 'الفصل الدراسي',
                    'position' => 1,
                    'status' => ContentStatus::Live,
                ])->id,
        ]);
    }
}
