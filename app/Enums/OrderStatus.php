<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Returned = 'returned';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Confirmed => 'bg-primary-subtle text-primary',
            self::Processing => 'bg-info-subtle text-info',
            self::Shipped => 'bg-warning-subtle text-warning',
            self::Delivered => 'bg-success-subtle text-success',
            self::Cancelled => 'bg-danger-subtle text-danger',
            self::Returned => 'bg-secondary-subtle text-secondary',
        };
    }

    /**
     * A customer may only cancel while the order has not yet shipped.
     */
    public function isCancellable(): bool
    {
        return in_array($this, [self::Confirmed, self::Processing], true);
    }
}
