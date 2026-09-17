<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Admin = 'admin';
    case Merchant = 'merchant';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Admin => 'Admin',
            self::Merchant => 'Merchant',
        };
    }

    /**
     * Where a user of this role lands after signing in.
     */
    public function home(): string
    {
        return match ($this) {
            self::Customer => '/',
            self::Admin => '/admin',
            self::Merchant => '/merchant',
        };
    }
}
