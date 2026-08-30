<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTeacher() || $user->isAdmin();
    }

    public function view(User $user, Question $question): bool
    {
        return $this->owns($user, $question);
    }

    public function create(User $user): bool
    {
        return $user->isTeacher();
    }

    public function update(User $user, Question $question): bool
    {
        return $this->owns($user, $question);
    }

    public function delete(User $user, Question $question): bool
    {
        return $this->owns($user, $question);
    }

    private function owns(User $user, Question $question): bool
    {
        return $user->isAdmin() || ($user->isTeacher() && $question->isOwnedBy($user->id));
    }
}
