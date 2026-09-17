<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Order lines store the price, supplier and commission rate that applied when
 * the order was placed. These tests hold that guarantee: nothing that happens
 * to a product afterwards may change what an order was worth, or what a
 * merchant earned from it.
 */
class OrderTotalsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function placeOrder(Product $product, int $quantity = 2): Order
    {
        $customer = User::where('email', 'customer@mealsonwheels.test')->firstOrFail();

        $order = Order::create([
            'user_id' => $customer->id,
            'transaction_id' => 'TXN'.uniqid(),
            'total_amount' => $quantity * (float) $product->price,
            'status' => OrderStatus::Confirmed,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'seller_id' => $product->seller_id,
            'quantity' => $quantity,
            'unit_price' => $product->price,
            'commission_rate' => config('marketplace.commission_rate'),
        ]);

        return $order->fresh('items');
    }

    public function test_a_price_change_does_not_alter_a_placed_order(): void
    {
        $product = Product::where('slug', 'woof-treats-organic-dog-snacks-150g')->firstOrFail();
        $order = $this->placeOrder($product, 2);

        $originalSubtotal = $order->items->first()->subtotal;
        $this->assertSame(2000.00, $originalSubtotal);

        // The merchant triples the price after the sale.
        $product->update(['price' => 3000.00]);

        $order = $order->fresh('items');

        $this->assertSame(
            2000.00,
            $order->items->first()->subtotal,
            'the historical line re-priced itself from the live product'
        );
        $this->assertSame('1000.00', $order->items->first()->unit_price);
    }

    public function test_a_price_change_does_not_alter_merchant_earnings(): void
    {
        $product = Product::where('slug', 'woof-treats-organic-dog-snacks-150g')->firstOrFail();
        $order = $this->placeOrder($product, 2);

        $item = $order->items->first();

        // 2000 gross, 10% commission -> 200 to the platform, 1800 to the merchant.
        $this->assertSame(200.00, $item->commission);
        $this->assertSame(1800.00, $item->net_earnings);

        $product->update(['price' => 3000.00]);
        config(['marketplace.commission_rate' => 25]);

        $item = $order->fresh('items')->items->first();

        $this->assertSame(200.00, $item->commission, 'commission followed the new rate');
        $this->assertSame(1800.00, $item->net_earnings, 'earnings followed the new price');
    }

    public function test_changing_the_supplier_does_not_reassign_past_sales(): void
    {
        $product = Product::where('slug', 'woof-treats-organic-dog-snacks-150g')->firstOrFail();
        $originalSeller = $product->seller_id;

        $order = $this->placeOrder($product);

        $otherMerchant = User::where('email', 'whiskers@mealsonwheels.test')->firstOrFail();
        $product->update(['seller_id' => $otherMerchant->id]);

        $this->assertSame(
            $originalSeller,
            $order->fresh('items')->items->first()->seller_id,
            'a past sale was moved to a merchant who never supplied it'
        );
    }

    public function test_a_merchant_sees_only_their_own_share_of_a_basket(): void
    {
        $order = Order::with('items')->has('items', '>=', 3)->firstOrFail();

        $sellerIds = $order->items->pluck('seller_id')->unique();
        $shares = $sellerIds->map(fn ($id) => $order->subtotalForSeller($id));

        foreach ($shares as $share) {
            $this->assertLessThan(
                (float) $order->total_amount,
                $share,
                'a merchant share should never equal the whole basket'
            );
        }

        $this->assertEqualsWithDelta((float) $order->total_amount, $shares->sum(), 0.01);
    }

    public function test_soft_deleting_a_product_keeps_its_order_history_readable(): void
    {
        $product = Product::where('slug', 'woof-treats-organic-dog-snacks-150g')->firstOrFail();
        $order = $this->placeOrder($product);

        $product->delete();

        $item = $order->fresh('items')->items->first();

        $this->assertNotNull($item->product, 'the product fell off its own order line');
        $this->assertSame('Woof Treats Organic Dog Snacks 150g', $item->product->name);
        $this->assertSame(2000.00, $item->subtotal);
    }
}
