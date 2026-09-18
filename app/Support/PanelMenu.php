<?php

namespace App\Support;

use App\Models\User;

/**
 * The sidebar for the staff panels.
 *
 * Admins and merchants share one layout, one stylesheet and one sidebar
 * component; only this list differs between them. Adding a screen means adding
 * an entry here, not editing markup.
 */
class PanelMenu
{
    /**
     * @return array<int, array{label: string, icon: string, route: string}>
     */
    public static function for(User $user): array
    {
        return match (true) {
            $user->isAdmin() => self::admin(),
            $user->isMerchant() => self::merchant(),
            default => [],
        };
    }

    /**
     * @return array<int, array{label: string, icon: string, route: string}>
     */
    private static function admin(): array
    {
        return [
            ['label' => 'Dashboard', 'icon' => 'mdi mdi-speedometer', 'route' => 'admin.dashboard'],
            ['label' => 'Products', 'icon' => 'bx bxs-dog', 'route' => 'admin.products.index'],
            ['label' => 'Categories', 'icon' => 'bx bx-category', 'route' => 'admin.categories.index'],
        ];
    }

    /**
     * @return array<int, array{label: string, icon: string, route: string}>
     */
    private static function merchant(): array
    {
        return [
            ['label' => 'Dashboard', 'icon' => 'mdi mdi-speedometer', 'route' => 'merchant.dashboard'],
        ];
    }
}
