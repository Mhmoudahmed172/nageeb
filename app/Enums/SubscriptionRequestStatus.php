<?php

namespace App\Enums;

enum SubscriptionRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'معلّقة',
            self::Approved => 'موافق عليها',
            self::Rejected => 'مرفوضة',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'nageeb-badge nageeb-badge--secondary',
            self::Approved => 'nageeb-badge nageeb-badge--support',
            self::Rejected => 'nageeb-badge nageeb-badge--primary',
        };
    }
}
