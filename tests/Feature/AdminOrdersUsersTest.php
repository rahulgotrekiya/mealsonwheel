<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminOrdersUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@mealsonwheels.test')->firstOrFail();
    }

    private function customer(): User
    {
        return User::where('email', 'customer@mealsonwheels.test')->firstOrFail();
    }

    // Access

    public function test_a_guest_is_sent_to_sign_in(): void
    {
        $this->get('/admin/orders')->assertRedirect('/login');
        $this->get('/admin/users')->assertRedirect('/login');
    }

    public function test_a_customer_cannot_reach_these_screens(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/orders')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }

    public function test_a_merchant_cannot_reach_these_screens(): void
    {
        $this->actingAs(User::factory()->merchant()->create());

        $this->get('/admin/orders')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }

    // Orders

    public function test_the_order_list_renders(): void
    {
        $order = Order::firstOrFail();

        $this->actingAs($this->admin())->get('/admin/orders')
            ->assertOk()
            ->assertSee($order->transaction_id, false)
            ->assertSee($order->user->email, false);
    }

    public function test_orders_can_be_filtered_by_status(): void
    {
        $delivered = Order::where('status', OrderStatus::Delivered)->firstOrFail();
        $confirmed = Order::where('status', OrderStatus::Confirmed)->firstOrFail();

        $this->actingAs($this->admin())->get('/admin/orders?status=delivered')
            ->assertOk()
            ->assertSee($delivered->transaction_id, false)
            ->assertDontSee($confirmed->transaction_id, false);
    }

    public function test_orders_can_be_searched_by_transaction_reference(): void
    {
        $order = Order::firstOrFail();

        $this->actingAs($this->admin())->get('/admin/orders?search='.$order->transaction_id)
            ->assertOk()
            ->assertSee($order->transaction_id, false);
    }

    public function test_the_order_detail_shows_lines_suppliers_and_the_price_paid(): void
    {
        $order = Order::with('items.product', 'items.seller')->has('items', '>=', 3)->firstOrFail();

        $response = $this->actingAs($this->admin())->get("/admin/orders/{$order->id}")->assertOk();

        foreach ($order->items as $item) {
            $response->assertSee($item->product->name, false);
            // Escaped, because a supplier name may contain characters Blade
            // encodes — "Feather & Fur Traders" reaches the page as &amp;.
            $response->assertSee($item->seller->full_name);
            $response->assertSee(number_format($item->unit_price, 2), false);
        }
    }

    public function test_an_admin_can_move_an_order_through_its_lifecycle(): void
    {
        $order = Order::where('status', OrderStatus::Confirmed)->firstOrFail();

        foreach ([OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Delivered] as $next) {
            $this->actingAs($this->admin())
                ->patch("/admin/orders/{$order->id}/status", ['status' => $next->value])
                ->assertRedirect();

            $this->assertSame($next, $order->fresh()->status);
        }
    }

    public function test_an_unrecognised_status_is_refused(): void
    {
        $order = Order::firstOrFail();
        $before = $order->status;

        $this->actingAs($this->admin())
            ->patch("/admin/orders/{$order->id}/status", ['status' => 'teleported'])
            ->assertSessionHasErrors('status');

        $this->assertSame($before, $order->fresh()->status);
    }

    public function test_changing_a_status_is_not_a_get_request(): void
    {
        $order = Order::firstOrFail();

        // A link that changes data can be followed by anything that renders the
        // page, so the route accepts PATCH only.
        $this->actingAs($this->admin())
            ->get("/admin/orders/{$order->id}/status?status=delivered")
            ->assertMethodNotAllowed();
    }

    public function test_a_customer_cannot_change_an_order_status(): void
    {
        $order = Order::firstOrFail();
        $before = $order->status;

        $this->actingAs($this->customer())
            ->patch("/admin/orders/{$order->id}/status", ['status' => 'delivered'])
            ->assertForbidden();

        $this->assertSame($before, $order->fresh()->status);
    }

    // Users

    public function test_the_user_list_renders_every_role(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/users')->assertOk();

        $response->assertSee('admin@mealsonwheels.test', false);
        $response->assertSee('customer@mealsonwheels.test', false);
        $response->assertSee('pawsome@mealsonwheels.test', false);
    }

    public function test_the_user_list_can_be_filtered_by_role(): void
    {
        $this->actingAs($this->admin())->get('/admin/users?role=merchant')
            ->assertOk()
            ->assertSee('pawsome@mealsonwheels.test', false)
            ->assertDontSee('customer@mealsonwheels.test', false);
    }

    /**
     * No response may ever carry a password hash.
     *
     * A list or edit screen that echoes the stored hash hands an attacker every
     * account's hash for offline cracking, and it is the sort of leak that
     * nothing else fails loudly about.
     */
    public function test_no_screen_leaks_a_password_hash(): void
    {
        $customer = $this->customer();

        foreach (['/admin/users', "/admin/users/{$customer->id}/edit"] as $path) {
            $html = $this->actingAs($this->admin())->get($path)->assertOk()->getContent();

            $this->assertStringNotContainsString($customer->password, $html, "{$path} leaked a hash");
            $this->assertStringNotContainsString('$2y$', $html, "{$path} contains a bcrypt hash");
        }
    }

    public function test_an_admin_can_add_a_user(): void
    {
        $this->actingAs($this->admin())->post('/admin/users', [
            'firstname' => 'Neha',
            'lastname' => 'Verma',
            'email' => 'neha@example.test',
            'phone' => '9876500000',
            'role' => UserRole::Merchant->value,
            'status' => UserStatus::Active->value,
            'password' => 'correct-horse-battery',
        ])->assertRedirect('/admin/users');

        $user = User::where('email', 'neha@example.test')->firstOrFail();

        $this->assertSame(UserRole::Merchant, $user->role);
        $this->assertTrue(Hash::check('correct-horse-battery', $user->password));
    }

    public function test_adding_a_user_requires_a_password(): void
    {
        $this->actingAs($this->admin())->post('/admin/users', [
            'firstname' => 'Neha',
            'lastname' => 'Verma',
            'email' => 'neha@example.test',
            'role' => UserRole::Customer->value,
            'status' => UserStatus::Active->value,
        ])->assertSessionHasErrors('password');
    }

    /**
     * Leaving the password field empty on an edit keeps the current password.
     *
     * Storing a hash of the empty string here would leave the account reachable
     * with a blank password, and the owner locked out without being told.
     */
    public function test_an_empty_password_on_edit_keeps_the_existing_one(): void
    {
        $customer = $this->customer();
        $hashBefore = $customer->password;

        $this->actingAs($this->admin())->put("/admin/users/{$customer->id}", [
            'firstname' => 'Sachin',
            'lastname' => 'Tholiya',
            'email' => $customer->email,
            'role' => $customer->role->value,
            'status' => $customer->status->value,
            'password' => '',
        ])->assertRedirect('/admin/users');

        $this->assertSame($hashBefore, $customer->fresh()->password);
        $this->assertTrue(Hash::check('password', $customer->fresh()->password));
    }

    public function test_a_supplied_password_on_edit_replaces_the_old_one(): void
    {
        $customer = $this->customer();

        $this->actingAs($this->admin())->put("/admin/users/{$customer->id}", [
            'firstname' => 'Sachin',
            'lastname' => 'Tholiya',
            'email' => $customer->email,
            'role' => $customer->role->value,
            'status' => $customer->status->value,
            'password' => 'a-brand-new-secret',
        ]);

        $this->assertTrue(Hash::check('a-brand-new-secret', $customer->fresh()->password));
    }

    public function test_an_admin_can_suspend_and_restore_an_account(): void
    {
        $merchant = User::where('email', 'pawsome@mealsonwheels.test')->firstOrFail();

        $payload = fn (UserStatus $status) => [
            'firstname' => $merchant->firstname,
            'lastname' => $merchant->lastname,
            'email' => $merchant->email,
            'role' => $merchant->role->value,
            'status' => $status->value,
        ];

        $this->actingAs($this->admin())->put("/admin/users/{$merchant->id}", $payload(UserStatus::Suspended));
        $this->assertSame(UserStatus::Suspended, $merchant->fresh()->status);

        // A suspended account is turned away on its very next request.
        $this->actingAs($merchant->fresh())->get('/merchant')->assertRedirect('/login');

        $this->actingAs($this->admin())->put("/admin/users/{$merchant->id}", $payload(UserStatus::Active));
        $this->assertSame(UserStatus::Active, $merchant->fresh()->status);
    }

    // Guards

    public function test_removing_a_customer_keeps_their_orders(): void
    {
        $customer = $this->customer();
        $orderCount = $customer->orders()->count();

        $this->assertGreaterThan(0, $orderCount);

        $this->actingAs($this->admin())
            ->delete("/admin/users/{$customer->id}")
            ->assertRedirect('/admin/users');

        $this->assertSoftDeleted('users', ['id' => $customer->id]);
        $this->assertSame($orderCount, Order::where('user_id', $customer->id)->count());
    }

    public function test_an_admin_cannot_remove_their_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->delete("/admin/users/{$admin->id}")
            ->assertSessionHasErrors('user');

        $this->assertNotSoftDeleted('users', ['id' => $admin->id]);
    }

    public function test_an_admin_cannot_demote_themselves(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
            'firstname' => $admin->firstname,
            'lastname' => $admin->lastname,
            'email' => $admin->email,
            'role' => UserRole::Customer->value,
            'status' => UserStatus::Suspended->value,
        ]);

        $admin->refresh();

        $this->assertTrue($admin->isAdmin(), 'the admin demoted themselves out of the panel');
        $this->assertSame(UserStatus::Active, $admin->status);
    }

    public function test_an_email_must_stay_unique(): void
    {
        $customer = $this->customer();

        $this->actingAs($this->admin())->put("/admin/users/{$customer->id}", [
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'email' => 'admin@mealsonwheels.test',
            'role' => $customer->role->value,
            'status' => $customer->status->value,
        ])->assertSessionHasErrors('email');
    }
}
