<?php

namespace App\Enums;

enum ExamStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::Published => 'منشور',
            self::Archived => 'مؤرشف',
        };
    }

    public function isVisibleToStudents(): bool
    {
        return $this === self::Published;
    }
}
