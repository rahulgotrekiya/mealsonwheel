<?php

namespace App\Support;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;

/**
 * The sidebar for the staff panels.
 *
 * Admins and merchants share one layout, one stylesheet and one sidebar
 * component; only this list differs between them. Adding a screen means adding
 * an entry here, not editing markup.
 *
 * An entry may carry a `badge` closure. It is resolved only for the role that
 * sees the entry, so a merchant never runs an admin's counting query.
 */
class PanelMenu
{
    /**
     * @return array<int, array{label: string, icon: string, route: string, badge?: int}>
     */
    public static function for(User $user): array
    {
        $items = match (true) {
            $user->isAdmin() => self::admin(),
            $user->isMerchant() => self::merchant(),
            default => [],
        };

        return array_map(function (array $item) {
            if (isset($item['badge'])) {
                $item['badge'] = ($item['badge'])();
            }

            return $item;
        }, $items);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function admin(): array
    {
        return [
            ['label' => 'Dashboard', 'icon' => 'mdi mdi-speedometer', 'route' => 'admin.dashboard'],
            ['label' => 'Products', 'icon' => 'bx bxs-dog', 'route' => 'admin.products.index'],
            ['label' => 'Categories', 'icon' => 'bx bx-category', 'route' => 'admin.categories.index'],
            [
                'label' => 'Reviews',
                'icon' => 'bx bx-check-shield',
                'route' => 'admin.reviews.index',
                'badge' => fn () => Product::where('status', ProductStatus::Pending)->count(),
            ],
            ['label' => 'Orders', 'icon' => 'ri-shopping-bag-3-line', 'route' => 'admin.orders.index'],
            [
                'label' => 'Merchants',
                'icon' => 'bx bx-store',
                'route' => 'admin.merchants.index',
                // Surfaces waiting applications without having to open the screen.
                'badge' => fn () => User::awaitingApproval()->count(),
            ],
            ['label' => 'Users', 'icon' => 'mdi mdi-account-group-outline', 'route' => 'admin.users.index'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function merchant(): array
    {
        return [
            ['label' => 'Dashboard', 'icon' => 'mdi mdi-speedometer', 'route' => 'merchant.dashboard'],
            ['label' => 'My Products', 'icon' => 'bx bxs-dog', 'route' => 'merchant.products.index'],
            ['label' => 'Stock', 'icon' => 'bx bx-box', 'route' => 'merchant.stock.index'],
        ];
    }
}
