<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Events\OrderCancelled;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('shop.orders.index', [
            'orders' => auth()->user()->orders()->latest()->paginate(15),
        ]);
    }

    public function show(Order $order): View
    {
        // Scoped by policy rather than by the query alone, so the reason an
        // order is unreachable is stated in one place.
        $this->authorize('view', $order);

        return view('shop.orders.show', [
            'order' => $order->load('items.product'),
        ]);
    }

    public function cancel(Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        $order->update(['status' => OrderStatus::Cancelled]);

        OrderCancelled::dispatch($order);

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Your order has been cancelled.');
    }
}
