<?php

namespace App\Enums;

enum ContentStatus: string
{
    case Draft = 'draft';
    case Live = 'live';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::Live => 'منشور',
            self::Archived => 'مؤرشف',
        };
    }
}
