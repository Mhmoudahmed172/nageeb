<?php

namespace App\Enums;

enum GradeLevel: string
{
    case Tenth = 'عاشر';
    case Eleventh = 'حادي عشر';
    case Twelfth = 'توجيهي';

    public function label(): string
    {
        return match ($this) {
            self::Tenth => 'الصف العاشر',
            self::Eleventh => 'الصف الحادي عشر',
            self::Twelfth => 'الثاني عشر / توجيهي',
        };
    }
}
