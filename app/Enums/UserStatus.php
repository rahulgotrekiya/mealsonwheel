<?php

namespace App\Enums;

enum UserStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending approval',
            self::Active => 'Active',
            self::Suspended => 'Suspended',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-warning-subtle text-warning',
            self::Active => 'bg-success-subtle text-success',
            self::Suspended => 'bg-danger-subtle text-danger',
        };
    }

    /**
     * Why a user in this state cannot sign in.
     */
    public function blockedMessage(): string
    {
        return match ($this) {
            self::Pending => 'Your merchant account is awaiting approval. We will email you once an administrator has reviewed it.',
            self::Suspended => 'This account has been suspended. Please contact support.',
            self::Active => '',
        };
    }
}
