<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Events\OrderPlaced;
use App\Exceptions\CheckoutException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns a basket into an order.
 *
 * Everything here happens inside one transaction: the order, its lines, the
 * stock it consumes and the emptying of the basket either all land together or
 * none of them do. A failure part way through must not be able to leave a
 * half-written order behind alongside a basket the customer still holds.
 */
class PlaceOrder
{
    public function __construct(private readonly Cart $cart) {}

    /**
     * @throws CheckoutException when the basket is empty or stock ran out
     */
    public function __invoke(User $customer): Order
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            throw new CheckoutException('Your basket is empty.');
        }

        $commissionRate = (float) config('marketplace.commission_rate');

        $order = DB::transaction(function () use ($customer, $lines, $commissionRate) {
            $order = Order::create([
                'user_id' => $customer->id,
                'transaction_id' => 'TXN'.Str::upper(Str::random(13)),
                'total_amount' => 0,
                'status' => OrderStatus::Confirmed,
            ]);

            $total = 0.0;

            foreach ($lines as $line) {
                /*
                 * Re-read the product inside the transaction and hold the row.
                 * The price shown at checkout and the stock counted a moment ago
                 * may both have moved, and two customers may be buying the last
                 * unit at the same time.
                 */
                $product = Product::query()
                    ->lockForUpdate()
                    ->find($line['product']->id);

                if (! $product || ! $product->isApproved()) {
                    throw new CheckoutException(
                        "{$line['product']->name} is no longer available."
                    );
                }

                if ($product->stock < $line['quantity']) {
                    throw new CheckoutException(
                        $product->stock > 0
                            ? "Only {$product->stock} of {$product->name} left in stock."
                            : "{$product->name} is out of stock."
                    );
                }

                // Price, supplier and commission rate are fixed at this moment
                // and never read from the product again.
                $order->items()->create([
                    'product_id' => $product->id,
                    'seller_id' => $product->seller_id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $product->price,
                    'commission_rate' => $commissionRate,
                ]);

                $product->decrement('stock', $line['quantity']);

                $total += $line['quantity'] * (float) $product->price;
            }

            $order->update(['total_amount' => round($total, 2)]);

            $this->cart->clear();

            return $order;
        });

        // Dispatched only once the transaction has committed, so the
        // confirmation email can never describe an order that was rolled back.
        OrderPlaced::dispatch($order);

        return $order->load('items.product', 'user');
    }
}
