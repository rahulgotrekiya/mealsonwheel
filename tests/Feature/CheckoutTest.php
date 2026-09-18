<?php

namespace Tests\Feature;

use App\Actions\PlaceOrder;
use App\Enums\OrderStatus;
use App\Exceptions\CheckoutException;
use App\Mail\OrderCancellation;
use App\Mail\OrderConfirmation;
use App\Models\Address;
use App\Models\Cart as CartLine;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Mail::fake();
    }

    private function customer(): User
    {
        return User::where('email', 'customer@mealsonwheels.test')->firstOrFail();
    }

    private function product(): Product
    {
        return Product::approved()->where('stock', '>', 10)->firstOrFail();
    }

    /**
     * @return array{0: User, 1: Product}
     */
    private function customerWithBasket(int $quantity = 2): array
    {
        $customer = $this->customer();
        $product = $this->product();

        CartLine::updateOrCreate(
            ['user_id' => $customer->id, 'product_id' => $product->id],
            ['quantity' => $quantity]
        );

        return [$customer, $product];
    }

    private const VALID_CARD = [
        'card_number' => '4111 1111 1111 1111',
        'expiry_date' => '12/34',
        'cvv' => '123',
    ];

    // Reaching checkout

    public function test_checkout_requires_signing_in(): void
    {
        $this->get('/checkout')->assertRedirect('/login');
    }

    public function test_staff_cannot_reach_checkout(): void
    {
        $admin = User::where('email', 'admin@mealsonwheels.test')->firstOrFail();

        $this->actingAs($admin)->get('/checkout')->assertForbidden();
    }

    public function test_an_empty_basket_sends_you_back_to_the_cart(): void
    {
        $this->actingAs($this->customer())->get('/checkout')->assertRedirect('/cart');
    }

    public function test_checkout_lists_the_basket_and_total(): void
    {
        [$customer, $product] = $this->customerWithBasket();

        $this->actingAs($customer)->get('/checkout')
            ->assertOk()
            ->assertSee($product->name, false)
            ->assertSee(number_format($product->price * 2, 2), false);
    }

    public function test_paying_without_a_delivery_address_is_refused(): void
    {
        [$customer] = $this->customerWithBasket();

        // Deleted through the query builder so the acting user does not keep a
        // stale relation loaded from before.
        Address::where('user_id', $customer->id)->delete();

        $this->actingAs($customer)->get('/checkout/payment')
            ->assertRedirect(route('checkout'))
            ->assertSessionHasErrors('street');
    }

    public function test_billing_details_are_saved(): void
    {
        [$customer] = $this->customerWithBasket();

        $this->actingAs($customer)->post('/checkout/billing', [
            'firstname' => 'Sachin',
            'lastname' => 'Tholiya',
            'phone' => '9999911111',
            'street' => '4 New Road',
            'city' => 'Surat',
            'state' => 'Gujarat',
            'zip_code' => '395007',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('users', ['id' => $customer->id, 'phone' => '9999911111']);
        $this->assertDatabaseHas('addresses', ['user_id' => $customer->id, 'city' => 'Surat']);
    }

    // Placing the order

    public function test_a_customer_can_complete_a_purchase(): void
    {
        [$customer, $product] = $this->customerWithBasket(2);
        $stockBefore = $product->stock;

        $this->actingAs($customer)
            ->post('/checkout/payment', self::VALID_CARD)
            ->assertRedirect();

        $order = Order::latest('id')->first();

        $this->assertSame($customer->id, $order->user_id);
        $this->assertSame(OrderStatus::Confirmed, $order->status);
        $this->assertSame(
            number_format($product->price * 2, 2, '.', ''),
            $order->total_amount
        );

        $this->assertSame($stockBefore - 2, $product->fresh()->stock, 'stock was not decremented');
        $this->assertSame(0, CartLine::where('user_id', $customer->id)->count(), 'basket was not emptied');
    }

    public function test_order_lines_record_price_supplier_and_commission_at_the_time_of_sale(): void
    {
        [$customer, $product] = $this->customerWithBasket(2);

        $this->actingAs($customer)->post('/checkout/payment', self::VALID_CARD);

        $item = OrderItem::latest('id')->first();

        $this->assertSame(number_format($product->price, 2, '.', ''), $item->unit_price);
        $this->assertSame($product->seller_id, $item->seller_id);
        $this->assertSame(
            number_format((float) config('marketplace.commission_rate'), 2, '.', ''),
            $item->commission_rate
        );
    }

    public function test_the_transaction_reference_is_unique_per_order(): void
    {
        $references = [];

        foreach (range(1, 3) as $ignored) {
            [$customer] = $this->customerWithBasket(1);
            $this->actingAs($customer)->post('/checkout/payment', self::VALID_CARD);
            $references[] = Order::latest('id')->first()->transaction_id;
        }

        $this->assertCount(3, array_unique($references));
    }

    public function test_a_confirmation_email_is_sent_once_the_order_exists(): void
    {
        [$customer] = $this->customerWithBasket();

        $this->actingAs($customer)->post('/checkout/payment', self::VALID_CARD);

        Mail::assertSent(OrderConfirmation::class, 1);
    }

    public function test_revisiting_the_confirmation_page_does_not_resend_the_email(): void
    {
        [$customer] = $this->customerWithBasket();

        $this->actingAs($customer)->post('/checkout/payment', self::VALID_CARD);
        $order = Order::latest('id')->first();

        $this->actingAs($customer)->get(route('checkout.confirmation', $order))->assertOk();
        $this->actingAs($customer)->get(route('checkout.confirmation', $order))->assertOk();

        // The email follows the order being created, not the page being rendered.
        Mail::assertSent(OrderConfirmation::class, 1);
    }

    // Failure paths

    public function test_card_details_are_validated(): void
    {
        [$customer] = $this->customerWithBasket();

        $this->actingAs($customer)->post('/checkout/payment', [
            'card_number' => '123',
            'expiry_date' => '99/99',
            'cvv' => 'abc',
        ])->assertSessionHasErrors(['card_number', 'expiry_date', 'cvv']);

        $this->assertSame(2, Order::count(), 'an order was created despite invalid card details');
    }

    public function test_an_order_cannot_be_placed_for_more_than_the_stock_on_hand(): void
    {
        [$customer, $product] = $this->customerWithBasket(3);
        $product->update(['stock' => 1]);

        $this->actingAs($customer)
            ->post('/checkout/payment', self::VALID_CARD)
            ->assertRedirect('/cart')
            ->assertSessionHasErrors('payment');

        $this->assertSame(2, Order::count());
        $this->assertSame(1, $product->fresh()->stock, 'stock moved despite the order failing');
    }

    public function test_a_failure_part_way_through_leaves_nothing_behind(): void
    {
        [$customer, $product] = $this->customerWithBasket(2);

        $second = Product::approved()->where('id', '!=', $product->id)->where('stock', '>', 0)->firstOrFail();
        CartLine::create(['user_id' => $customer->id, 'product_id' => $second->id, 'quantity' => 1]);

        $ordersBefore = Order::count();
        $itemsBefore = OrderItem::count();
        $stockBefore = $product->stock;

        // The second line cannot be satisfied, so the first must not survive.
        $second->update(['stock' => 0]);

        try {
            $this->actingAs($customer);
            app(PlaceOrder::class)($customer);
            $this->fail('expected the order to be refused');
        } catch (CheckoutException) {
            // expected
        }

        $this->assertSame($ordersBefore, Order::count(), 'a partial order survived');
        $this->assertSame($itemsBefore, OrderItem::count(), 'orphan order lines survived');
        $this->assertSame($stockBefore, $product->fresh()->stock, 'stock was consumed by a failed order');
        $this->assertSame(2, CartLine::where('user_id', $customer->id)->count(), 'the basket was emptied anyway');
    }

    // Order history

    public function test_a_customer_sees_their_own_orders(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)->get('/orders')
            ->assertOk()
            ->assertSee('#'.$customer->orders()->first()->id, false);
    }

    public function test_one_customer_cannot_read_another_customers_order(): void
    {
        $order = $this->customer()->orders()->firstOrFail();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get(route('orders.show', $order))->assertForbidden();
    }

    public function test_an_order_detail_shows_the_price_paid_not_the_price_now(): void
    {
        [$customer, $product] = $this->customerWithBasket(2);
        $this->actingAs($customer)->post('/checkout/payment', self::VALID_CARD);

        $order = Order::latest('id')->first();
        $paid = number_format($product->price * 2, 2);

        $product->update(['price' => 9999.00]);

        $this->actingAs($customer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($paid, false)
            ->assertDontSee('9,999.00', false);
    }

    // Cancelling

    public function test_a_customer_can_cancel_an_order_that_has_not_shipped(): void
    {
        [$customer] = $this->customerWithBasket();
        $this->actingAs($customer)->post('/checkout/payment', self::VALID_CARD);
        $order = Order::latest('id')->first();

        $this->actingAs($customer)
            ->patch(route('orders.cancel', $order))
            ->assertRedirect(route('orders.show', $order));

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        Mail::assertSent(OrderCancellation::class, 1);
    }

    public function test_a_shipped_order_cannot_be_cancelled(): void
    {
        [$customer] = $this->customerWithBasket();
        $this->actingAs($customer)->post('/checkout/payment', self::VALID_CARD);

        $order = Order::latest('id')->first();
        $order->update(['status' => OrderStatus::Shipped]);

        $this->actingAs($customer)->patch(route('orders.cancel', $order))->assertForbidden();

        $this->assertSame(OrderStatus::Shipped, $order->fresh()->status);
    }

    public function test_one_customer_cannot_cancel_another_customers_order(): void
    {
        $order = $this->customer()->orders()->where('status', OrderStatus::Confirmed)->firstOrFail();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->patch(route('orders.cancel', $order))->assertForbidden();

        $this->assertSame(OrderStatus::Confirmed, $order->fresh()->status);
    }

    // Account

    public function test_a_customer_can_update_their_details(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)->patch('/account', [
            'firstname' => 'Sachin',
            'lastname' => 'Tholiya',
            'phone' => '9000000000',
            'street' => '9 Ring Road',
            'city' => 'Rajkot',
            'state' => 'Gujarat',
            'zip_code' => '360001',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('addresses', ['user_id' => $customer->id, 'city' => 'Rajkot']);
    }

    public function test_updating_details_cannot_change_the_account_role(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)->patch('/account', [
            'firstname' => 'Sachin',
            'lastname' => 'Tholiya',
            'phone' => '9000000000',
            'street' => '9 Ring Road',
            'city' => 'Rajkot',
            'state' => 'Gujarat',
            'zip_code' => '360001',
            'role' => 'admin',
        ]);

        $this->assertTrue($customer->fresh()->isCustomer());
    }
}
