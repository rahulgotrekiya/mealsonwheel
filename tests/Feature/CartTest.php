<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Cart as CartLine;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function product(): Product
    {
        return Product::approved()->where('stock', '>', 5)->firstOrFail();
    }

    private function customer(): User
    {
        return User::where('email', 'customer@mealsonwheels.test')->firstOrFail();
    }

    // Guest basket

    public function test_a_guest_can_add_to_the_basket(): void
    {
        $product = $this->product();

        $this->post('/cart/add', ['id' => $product->id, 'quantity' => 2])
            ->assertOk()
            ->assertJson(['error' => false]);

        $this->post('/cart/fetch')->assertJson(['count' => 1, 'total_quantity' => 2]);
    }

    public function test_a_guest_basket_is_kept_in_the_session_not_the_database(): void
    {
        $this->post('/cart/add', ['id' => $this->product()->id, 'quantity' => 1]);

        $this->assertSame(0, CartLine::count());
        $this->assertNotEmpty(session('cart'));
    }

    public function test_adding_the_same_product_again_raises_the_quantity(): void
    {
        $product = $this->product();

        $this->post('/cart/add', ['id' => $product->id, 'quantity' => 1]);
        $this->post('/cart/add', ['id' => $product->id, 'quantity' => 2])
            ->assertJson(['error' => false]);

        $this->post('/cart/fetch')->assertJson(['count' => 1, 'total_quantity' => 3]);
    }

    public function test_a_guest_can_change_quantity_and_remove_items(): void
    {
        $product = $this->product();
        $this->post('/cart/add', ['id' => $product->id, 'quantity' => 1]);

        $this->post('/cart/update', ['id' => $product->id, 'qty' => 3])->assertJson(['error' => false]);
        $this->post('/cart/fetch')->assertJson(['total_quantity' => 3]);

        $this->post('/cart/remove', ['id' => $product->id])->assertJson(['error' => false]);
        $this->post('/cart/fetch')->assertJson(['count' => 0, 'total_quantity' => 0]);
    }

    // Limits

    public function test_the_basket_caps_the_quantity_per_product(): void
    {
        $max = (int) config('marketplace.max_quantity_per_item');
        $product = $this->product();

        $this->post('/cart/add', ['id' => $product->id, 'quantity' => 99]);
        $this->post('/cart/fetch')->assertJson(['total_quantity' => $max]);

        // Server side, not only in the page's JavaScript.
        $this->post('/cart/update', ['id' => $product->id, 'qty' => 99])
            ->assertJson(['error' => true]);
    }

    public function test_the_basket_will_not_exceed_available_stock(): void
    {
        $product = $this->product();
        $product->update(['stock' => 2]);

        $response = $this->post('/cart/add', ['id' => $product->id, 'quantity' => 3]);

        $response->assertJson(['error' => true]);
        $this->assertStringContainsString('Only 2 left', $response->json('message'));
    }

    public function test_an_out_of_stock_product_cannot_be_added(): void
    {
        $product = $this->product();
        $product->update(['stock' => 0]);

        $this->post('/cart/add', ['id' => $product->id, 'quantity' => 1])
            ->assertJson(['error' => true, 'message' => 'That product is out of stock.']);
    }

    public function test_an_unreviewed_product_cannot_be_added(): void
    {
        $product = $this->product();
        $product->update(['status' => ProductStatus::Pending]);

        $this->post('/cart/add', ['id' => $product->id, 'quantity' => 1])
            ->assertJson(['error' => true, 'message' => 'That product is not available.']);
    }

    public function test_adding_an_unknown_product_is_rejected(): void
    {
        $this->post('/cart/add', ['id' => 999999, 'quantity' => 1])
            ->assertSessionHasErrors('id');
    }

    // Signed-in basket

    public function test_a_signed_in_customer_basket_is_stored_in_the_database(): void
    {
        $product = $this->product();

        $this->actingAs($this->customer())
            ->post('/cart/add', ['id' => $product->id, 'quantity' => 2])
            ->assertJson(['error' => false]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->customer()->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_a_signed_in_basket_survives_signing_out_and_back_in(): void
    {
        $customer = $this->customer();
        $product = $this->product();

        $this->actingAs($customer)->post('/cart/add', ['id' => $product->id, 'quantity' => 2]);

        $this->post('/logout');
        $this->post('/login', ['email' => $customer->email, 'password' => 'password']);

        $this->post('/cart/fetch')->assertJson(['total_quantity' => 2]);
    }

    // Merging on sign-in

    public function test_a_guest_basket_follows_the_visitor_into_their_account(): void
    {
        $customer = $this->customer();
        $product = $this->product();

        $this->post('/cart/add', ['id' => $product->id, 'quantity' => 2]);
        $this->post('/login', ['email' => $customer->email, 'password' => 'password']);

        $this->assertDatabaseHas('carts', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $this->assertEmpty(session('cart'), 'the guest basket should be cleared once merged');
    }

    public function test_merging_combines_quantities_rather_than_replacing_them(): void
    {
        $customer = $this->customer();
        $product = $this->product();

        CartLine::create(['user_id' => $customer->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->post('/cart/add', ['id' => $product->id, 'quantity' => 2]);
        $this->post('/login', ['email' => $customer->email, 'password' => 'password']);

        $this->assertDatabaseHas('carts', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_merging_still_respects_the_per_product_cap(): void
    {
        $max = (int) config('marketplace.max_quantity_per_item');
        $customer = $this->customer();
        $product = $this->product();

        CartLine::create(['user_id' => $customer->id, 'product_id' => $product->id, 'quantity' => $max]);

        $this->post('/cart/add', ['id' => $product->id, 'quantity' => $max]);
        $this->post('/login', ['email' => $customer->email, 'password' => 'password']);

        $this->assertSame(
            $max,
            (int) CartLine::where('user_id', $customer->id)->where('product_id', $product->id)->value('quantity')
        );
    }

    // Ownership

    public function test_one_customer_cannot_touch_another_customers_basket(): void
    {
        $victim = $this->customer();
        $product = $this->product();

        CartLine::create(['user_id' => $victim->id, 'product_id' => $product->id, 'quantity' => 3]);

        $attacker = User::factory()->create();

        $this->actingAs($attacker)->post('/cart/remove', ['id' => $product->id])->assertOk();
        $this->actingAs($attacker)->post('/cart/update', ['id' => $product->id, 'qty' => 1]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $victim->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_a_guest_basket_does_not_leak_into_another_visitors_session(): void
    {
        $product = $this->product();

        $this->post('/cart/add', ['id' => $product->id, 'quantity' => 2]);
        $this->post('/cart/fetch')->assertJson(['total_quantity' => 2]);

        $this->flushSession();

        $this->post('/cart/fetch')->assertJson(['count' => 0, 'total_quantity' => 0]);
    }

    // The page

    public function test_the_basket_page_renders(): void
    {
        $this->get('/cart')->assertOk()->assertSee('Your cart is empty', false);
    }

    public function test_the_basket_page_offers_checkout_only_once_signed_in(): void
    {
        $this->get('/cart')->assertSee('Login To Checkout', false);

        $this->actingAs($this->customer())->get('/cart')->assertSee('Check out', false);
    }

    public function test_the_rows_endpoint_returns_nothing_for_an_empty_basket(): void
    {
        $this->assertSame('', $this->post('/cart/details')->assertOk()->json());
    }

    public function test_the_rows_endpoint_renders_the_basket(): void
    {
        $product = $this->product();
        $this->post('/cart/add', ['id' => $product->id, 'quantity' => 2]);

        $html = $this->post('/cart/details')->json();

        $this->assertStringContainsString($product->name, $html);
        $this->assertStringContainsString('qty_'.$product->id, $html);
        $this->assertStringContainsString(number_format($product->price * 2, 2), $html);
    }

    public function test_the_total_endpoint_answers_guests_too(): void
    {
        $product = $this->product();

        // JSON has one number type, so a whole total arrives back as an int.
        $this->assertSame(0.0, (float) $this->post('/cart/total')->assertOk()->json());

        $this->post('/cart/add', ['id' => $product->id, 'quantity' => 2]);

        $this->assertSame(
            round((float) $product->price * 2, 2),
            (float) $this->post('/cart/total')->assertOk()->json()
        );
    }

    public function test_the_endpoints_require_a_session_token(): void
    {
        // CSRF protection is disabled in the test harness by default, so assert
        // the route actually sits behind the web middleware group that applies it.
        $route = app('router')->getRoutes()->getByName('cart.add');

        $this->assertContains('web', $route->gatherMiddleware());
    }
}
