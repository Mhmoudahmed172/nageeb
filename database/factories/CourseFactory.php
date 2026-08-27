<?php

namespace Database\Factories;

use App\Enums\CourseStatus;
use App\Enums\GradeLevel;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'teacher_id' => User::factory()->teacher(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('####'),
            'description' => fake()->paragraph(),
            'grade_level' => GradeLevel::Tenth,
            'status' => CourseStatus::Live,
            'is_free' => false,
            'reference_price' => null,
            'cover_image' => null,
        ];
    }
}
