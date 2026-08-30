<?php

namespace App\Support;

use App\Enums\AttemptStatus;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ExamAttemptService
{
    /**
     * Resumes the open attempt when there is one so a refresh or a closed tab
     * never costs the student their progress or an extra attempt.
     */
    public function startOrResume(User $student, Exam $exam): ExamAttempt
    {
        $open = $exam->openAttemptFor($student);

        if ($open) {
            return $this->closeIfTimedOut($open);
        }

        abort_unless($exam->studentHasAttemptsLeft($student), 403);

        return DB::transaction(function () use ($student, $exam): ExamAttempt {
            $number = (int) $exam->attempts()->where('student_id', $student->id)->max('attempt_number') + 1;
            $startedAt = now();

            return $exam->attempts()->create([
                'student_id' => $student->id,
                'attempt_number' => $number,
                'status' => AttemptStatus::InProgress,
                'started_at' => $startedAt,
                'expires_at' => $exam->duration_minutes
                    ? $startedAt->copy()->addMinutes((int) $exam->duration_minutes)
                    : null,
                'total_points' => $exam->totalPoints(),
            ]);
        });
    }

    /**
     * @param  array<int, int|string>  $optionIds
     */
    public function saveAnswer(ExamAttempt $attempt, Question $question, array $optionIds): ExamAnswer
    {
        $allowed = $question->options->pluck('id')->map(fn ($id) => (int) $id);
        $selected = collect($optionIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $allowed->contains($id))
            ->unique()
            ->sort()
            ->values();

        if (! $question->type->allowsMultipleCorrectOptions()) {
            $selected = $selected->take(1)->values();
        }

        // Auto-gradable answers are graded as they are saved so a resumed or
        // timed-out attempt already carries its scoring. Submission recomputes
        // everything anyway, including the questions left unanswered.
        $isCorrect = $selected->all() === $question->correctOptionIds()->all();

        return ExamAnswer::query()->updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ],
            [
                'selected_option_ids' => $selected->all(),
                'is_correct' => $isCorrect,
                'points_awarded' => $isCorrect ? $attempt->exam->pointsFor($question) : 0,
            ],
        );
    }

    public function closeIfTimedOut(ExamAttempt $attempt): ExamAttempt
    {
        if ($attempt->isInProgress() && $attempt->hasTimedOut()) {
            return $this->submit($attempt, AttemptStatus::Expired);
        }

        return $attempt;
    }

    public function submit(ExamAttempt $attempt, AttemptStatus $status = AttemptStatus::Submitted): ExamAttempt
    {
        if (! $attempt->isInProgress()) {
            return $attempt;
        }

        $exam = $attempt->exam()->with('questions.options')->first();
        $attempt->load('answers');

        $score = 0.0;
        $total = 0.0;

        foreach ($exam->questions as $question) {
            $points = $exam->pointsFor($question);
            $total += $points;

            $answer = $attempt->answers->firstWhere('question_id', $question->id)
                ?? new ExamAnswer([
                    'exam_attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'selected_option_ids' => [],
                ]);

            $selected = collect($answer->selectedIds())->sort()->values()->all();
            $correct = $question->correctOptionIds()->all();
            $isCorrect = $selected !== [] && $selected === $correct;
            $awarded = $isCorrect ? $points : 0.0;

            $answer->fill([
                'exam_attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'is_correct' => $isCorrect,
                'points_awarded' => $awarded,
            ])->save();

            $score += $awarded;
        }

        $percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0.0;

        $attempt->update([
            'status' => $status,
            'submitted_at' => now(),
            'score' => $score,
            'total_points' => $total,
            'percentage' => $percentage,
            'passed' => $percentage >= (float) $exam->passing_score,
        ]);

        return $attempt->refresh();
    }
}
