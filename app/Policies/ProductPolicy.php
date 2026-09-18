<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * Who may act on a product.
 *
 * Admins manage the whole catalog. Merchants manage only what they supply, and
 * that ownership check is enforced here rather than in each controller, so the
 * merchant panel cannot reach another supplier's listing by guessing an id.
 */
class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isMerchant();
    }

    public function view(User $user, Product $product): bool
    {
        return $this->owns($user, $product);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isMerchant();
    }

    public function update(User $user, Product $product): bool
    {
        return $this->owns($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->owns($user, $product);
    }

    /**
     * Only an admin may pass judgement on a listing.
     */
    public function review(User $user): bool
    {
        return $user->isAdmin();
    }

    private function owns(User $user, Product $product): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isMerchant() && $product->seller_id === $user->id;
    }
}
