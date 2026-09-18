<?php

namespace App\Enums;

use Carbon\Carbon;

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

    /**
     * What to tell the customer about arrival, given when the order was placed.
     */
    public function estimatedDelivery(\DateTimeInterface $placedAt): string
    {
        return match ($this) {
            self::Confirmed => Carbon::parse($placedAt)->addDays(5)->format('M d, Y'),
            self::Processing => Carbon::parse($placedAt)->addDays(4)->format('M d, Y'),
            self::Shipped => Carbon::parse($placedAt)->addDays(3)->format('M d, Y'),
            self::Delivered => 'Delivered',
            self::Cancelled => 'Order Cancelled',
            self::Returned => 'Order Returned',
        };
    }
}
