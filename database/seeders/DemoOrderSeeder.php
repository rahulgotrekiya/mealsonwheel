<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoOrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'customer@mealsonwheels.test')->firstOrFail();

        // Deliberately spans all three merchants, so the merchant views can be
        // shown filtering one basket down to each supplier's own lines.
        $this->placeOrder($customer, [
            'woof-treats-organic-dog-snacks-150g' => 1,
            'jomo-salmon-and-sweet-potato-dry-cat-food-400g' => 2,
            'excel-forage-and-feast-hay-bar-with-marigold' => 3,
        ], OrderStatus::Delivered, now()->subDays(12));

        $this->placeOrder($customer, [
            'paw-pupper-all-natural-dog-treats-150g' => 2,
            'me-o-creamy-treats-crab-for-cats-60g' => 4,
        ], OrderStatus::Confirmed, now()->subDays(2));
    }

    /**
     * @param  array<string, int>  $lines  product slug => quantity
     */
    private function placeOrder(User $customer, array $lines, OrderStatus $status, $placedAt): void
    {
        $products = Product::whereIn('slug', array_keys($lines))->get()->keyBy('slug');

        $missing = array_diff(array_keys($lines), $products->keys()->all());

        if ($missing !== []) {
            $this->command->warn('  skipped an order, unknown slugs: '.implode(', ', $missing));

            return;
        }

        $commissionRate = config('marketplace.commission_rate');

        $total = collect($lines)->sum(
            fn (int $qty, string $slug) => $qty * (float) $products[$slug]->price
        );

        $order = Order::create([
            'user_id' => $customer->id,
            'transaction_id' => 'TXN'.Str::upper(Str::random(13)),
            'total_amount' => $total,
            'status' => $status,
            'created_at' => $placedAt,
            'updated_at' => $placedAt,
        ]);

        foreach ($lines as $slug => $quantity) {
            $product = $products[$slug];

            // Price, supplier and commission rate are fixed here and never
            // read from the product again.
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'seller_id' => $product->seller_id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'commission_rate' => $commissionRate,
                'created_at' => $placedAt,
                'updated_at' => $placedAt,
            ]);

            $product->decrement('stock', $quantity);
        }
    }
}
