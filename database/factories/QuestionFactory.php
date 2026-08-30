<?php

namespace Database\Factories;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => User::factory()->teacher(),
            'course_id' => null,
            'unit_id' => null,
            'type' => QuestionType::MultipleChoice,
            'text' => 'ما ناتج ٢ + ٢؟',
            'explanation' => null,
            'points' => 1,
            'difficulty' => QuestionDifficulty::Medium,
            'data' => null,
        ];
    }

    /**
     * Creates the option set, marking the given indexes as the correct answers.
     *
     * @param  array<int, string>  $options
     * @param  array<int, int>  $correctIndexes
     */
    public function withOptions(array $options = ['٣', '٤', '٥'], array $correctIndexes = [1]): static
    {
        return $this->afterCreating(function (Question $question) use ($options, $correctIndexes): void {
            foreach ($options as $index => $text) {
                $question->options()->create([
                    'text' => $text,
                    'is_correct' => in_array($index, $correctIndexes, true),
                    'position' => $index + 1,
                ]);
            }
        });
    }

    public function trueFalse(bool $answerIsTrue = true): static
    {
        return $this->state(fn () => ['type' => QuestionType::TrueFalse])
            ->withOptions(['صح', 'خطأ'], [$answerIsTrue ? 0 : 1]);
    }

    public function multipleResponse(): static
    {
        return $this->state(fn () => ['type' => QuestionType::MultipleResponse])
            ->withOptions(['أ', 'ب', 'ج', 'د'], [0, 2]);
    }
}
