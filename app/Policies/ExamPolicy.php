<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\User;

class ExamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTeacher() || $user->isAdmin();
    }

    public function view(User $user, Exam $exam): bool
    {
        return $this->owns($user, $exam);
    }

    public function create(User $user): bool
    {
        return $user->isTeacher();
    }

    public function update(User $user, Exam $exam): bool
    {
        return $this->owns($user, $exam);
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $this->owns($user, $exam);
    }

    public function viewResults(User $user, Exam $exam): bool
    {
        return $this->owns($user, $exam);
    }

    public function viewAttempt(User $user, ExamAttempt $attempt): bool
    {
        $attempt->loadMissing('exam');

        return $this->owns($user, $attempt->exam);
    }

    /**
     * A question may only be attached to an exam owned by the same teacher.
     */
    public function attachQuestion(User $user, Exam $exam, Question $question): bool
    {
        return $this->owns($user, $exam) && $question->teacher_id === $exam->teacher_id;
    }

    private function owns(User $user, Exam $exam): bool
    {
        return $user->isAdmin() || ($user->isTeacher() && $exam->isOwnedBy($user->id));
    }
}
