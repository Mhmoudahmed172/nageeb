<?php

namespace App\Enums;

/**
 * Only auto-gradable choice types are supported for now. Additional types
 * (essay, ordering, matching, short answer) can be added here: questions carry
 * a free-form `data` payload and answers carry `text_answer` plus a nullable
 * `is_correct` so manual grading can be layered on without a schema change.
 */
enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case TrueFalse = 'true_false';
    case MultipleResponse = 'multiple_response';

    public function label(): string
    {
        return match ($this) {
            self::MultipleChoice => 'اختيار من متعدد',
            self::TrueFalse => 'صح / خطأ',
            self::MultipleResponse => 'اختيار متعدد الإجابات',
        };
    }

    public function allowsMultipleCorrectOptions(): bool
    {
        return $this === self::MultipleResponse;
    }

    public function isAutoGradable(): bool
    {
        return true;
    }
}
