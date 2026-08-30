<?php

namespace App\Enums;

enum ExamDeliveryMode: string
{
    case Interactive = 'interactive';
    case File = 'file';

    public function label(): string
    {
        return match ($this) {
            self::Interactive => 'اختبار تفاعلي',
            self::File => 'اختبار مرفق',
        };
    }
}
