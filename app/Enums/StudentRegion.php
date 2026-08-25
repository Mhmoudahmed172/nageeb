<?php

namespace App\Enums;

enum StudentRegion: string
{
    case Gaza = 'gaza';
    case WestBankAbroad = 'west_bank_abroad';

    public function label(): string
    {
        return match ($this) {
            self::Gaza => 'غزة',
            self::WestBankAbroad => 'الضفة الغربية والخارج',
        };
    }
}
