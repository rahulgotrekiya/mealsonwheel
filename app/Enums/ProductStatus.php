<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-warning-subtle text-warning',
            self::Approved => 'bg-success-subtle text-success',
            self::Rejected => 'bg-danger-subtle text-danger',
        };
    }
}
