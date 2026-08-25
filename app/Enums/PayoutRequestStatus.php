<?php

namespace App\Enums;

enum PayoutRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Settled = 'settled';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد المراجعة',
            self::Approved => 'موافق عليه',
            self::Settled => 'تمّت التسوية',
            self::Rejected => 'مرفوض',
        };
    }
}
