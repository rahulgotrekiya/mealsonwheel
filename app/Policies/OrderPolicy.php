<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * An order is readable only by the customer who placed it.
     *
     * Without this, an order id in the URL is enough to read somebody else's
     * name, address and purchase history.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->id === $order->user_id && $order->isCancellable();
    }
}
