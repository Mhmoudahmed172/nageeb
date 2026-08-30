<?php

namespace Database\Factories;

use App\Enums\ExamStatus;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $course = Course::factory();

        return [
            'course_id' => $course,
            'teacher_id' => fn (array $attributes) => Course::query()->find($attributes['course_id'])?->teacher_id,
            'semester_id' => null,
            'unit_id' => null,
            'lesson_id' => null,
            'title' => 'اختبار قصير',
            'description' => null,
            'duration_minutes' => 30,
            'max_attempts' => 1,
            'passing_score' => 50,
            'status' => ExamStatus::Draft,
            'show_results_immediately' => true,
            'show_correct_answers' => false,
            'shuffle_questions' => false,
            'shuffle_options' => false,
            'delivery_mode' => \App\Enums\ExamDeliveryMode::Interactive,
        ];
    }

    public function forCourse(Course $course): static
    {
        return $this->state(fn () => [
            'course_id' => $course->id,
            'teacher_id' => $course->teacher_id,
        ]);
    }

    public function forLesson(Lesson $lesson): static
    {
        $lesson->loadMissing('unit.semester.course');
        $semester = $lesson->unit->semester;

        return $this->state(fn () => [
            'course_id' => $semester->course_id,
            'teacher_id' => $semester->course->teacher_id,
            'semester_id' => $semester->id,
            'unit_id' => $lesson->unit_id,
            'lesson_id' => $lesson->id,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => ExamStatus::Published]);
    }

    public function filePaper(): static
    {
        return $this->state(fn () => [
            'delivery_mode' => \App\Enums\ExamDeliveryMode::File,
            'duration_minutes' => null,
            'max_attempts' => 1,
        ]);
    }
}
