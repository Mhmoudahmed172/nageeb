<?php

namespace Database\Factories;

use App\Enums\AttemptStatus;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamAttempt>
 */
class ExamAttemptFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'student_id' => User::factory()->student(),
            'attempt_number' => 1,
            'status' => AttemptStatus::InProgress,
            'started_at' => now(),
            'expires_at' => null,
            'submitted_at' => null,
            'score' => 0,
            'total_points' => 0,
            'percentage' => 0,
            'passed' => false,
        ];
    }

    public function submitted(float $percentage = 100): static
    {
        return $this->state(fn () => [
            'status' => AttemptStatus::Submitted,
            'submitted_at' => now(),
            'percentage' => $percentage,
            'passed' => $percentage >= 50,
        ]);
    }
}
