<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        return view('admin.orders.index', [
            'search' => $search,
            'status' => $status,
            'statuses' => OrderStatus::cases(),
            'orders' => Order::query()
                ->with('user')
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                    $q->where('transaction_id', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u
                            ->where('email', 'like', "%{$search}%")
                            ->orWhere('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%"));
                }))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load('items.product', 'items.seller', 'user.address'),
            'statuses' => OrderStatus::cases(),
        ]);
    }

    /**
     * Advance an order.
     *
     * The submitted value is checked against the enum rather than written
     * through: an unrecognised status would otherwise reach the column and be
     * silently coerced to something meaningless.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        // NOTE: any status may follow any other, so a mistake can be corrected.
        // Add a transition table here if the lifecycle ever needs enforcing.
        $order->update(['status' => $data['status']]);

        return back()->with('status', "Order #{$order->id} is now {$order->fresh()->status->label()}.");
    }
}
