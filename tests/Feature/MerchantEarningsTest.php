<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\SalesReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MerchantEarningsTest extends TestCase
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

    private function merchant(): User
    {
        return User::where('email', 'pawsome@mealsonwheels.test')->firstOrFail();
    }

    private function customer(): User
    {
        return User::where('email', 'customer@mealsonwheels.test')->firstOrFail();
    }

    // Access

    public function test_the_sales_page_needs_a_merchant(): void
    {
        $this->get('/merchant/sales')->assertRedirect('/login');
    }

    public function test_a_customer_cannot_see_merchant_sales(): void
    {
        $this->actingAs(User::factory()->create())->get('/merchant/sales')->assertForbidden();
    }

    public function test_a_merchant_cannot_see_the_admin_earnings_table(): void
    {
        $this->actingAs($this->merchant())->get('/admin/earnings')->assertForbidden();
    }

    // What a merchant sees

    public function test_a_merchant_sees_only_their_own_sold_lines(): void
    {
        $merchant = $this->merchant();

        $mine = OrderItem::forSeller($merchant->id)->with('product')->firstOrFail();
        $theirs = OrderItem::where('seller_id', '!=', $merchant->id)->with('product')->firstOrFail();

        $this->actingAs($merchant)->get('/merchant/sales')
            ->assertOk()
            ->assertSee($mine->product->name, false)
            ->assertDontSee($theirs->product->name, false);
    }

    /**
     * A supplier ships to the warehouse and never handles delivery, so nothing
     * identifying the customer belongs on their screen.
     */
    public function test_the_sales_page_exposes_no_customer_details(): void
    {
        $customer = $this->customer();
        $customer->load('address');

        $html = $this->actingAs($this->merchant())->get('/merchant/sales')->assertOk()->getContent();

        foreach ([
            $customer->email,
            $customer->firstname.' '.$customer->lastname,
            $customer->phone,
            $customer->address->street,
            $customer->address->city,
        ] as $detail) {
            $this->assertStringNotContainsString($detail, $html, "a customer detail leaked: {$detail}");
        }
    }

    public function test_the_sales_page_never_shows_the_whole_basket_total(): void
    {
        $merchant = $this->merchant();

        // An order this merchant supplied only part of.
        $order = Order::whereHas('items', fn ($q) => $q->where('seller_id', $merchant->id))
            ->has('items', '>=', 3)
            ->firstOrFail();

        $this->assertGreaterThan(
            $order->subtotalForSeller($merchant->id),
            (float) $order->total_amount,
            'the fixture should be an order spanning several suppliers'
        );

        $this->actingAs($merchant)->get('/merchant/sales')
            ->assertOk()
            ->assertDontSee(number_format($order->total_amount, 2), false);
    }

    public function test_the_sales_page_reports_the_merchants_own_figures(): void
    {
        $merchant = $this->merchant();
        $report = SalesReport::forSeller($merchant->id);

        $this->actingAs($merchant)->get('/merchant/sales')
            ->assertOk()
            ->assertSee(number_format($report->totalEarnings(), 2), false)
            ->assertSee(number_format($report->netEarnings(), 2), false)
            ->assertSee(number_format($report->commission(), 2), false);
    }

    public function test_a_line_shows_the_price_it_was_sold_at(): void
    {
        $merchant = $this->merchant();
        $line = OrderItem::forSeller($merchant->id)->with('product')->firstOrFail();

        $soldAt = number_format($line->unit_price, 2);

        $line->product->update(['price' => 8888.00]);

        $this->actingAs($merchant)->get('/merchant/sales')
            ->assertOk()
            ->assertSee($soldAt, false)
            ->assertDontSee('8,888.00', false);
    }

    // The arithmetic

    public function test_commission_and_earnings_add_back_up_to_gross(): void
    {
        $report = SalesReport::forSeller($this->merchant()->id);

        $this->assertEqualsWithDelta(
            $report->totalEarnings(),
            $report->commission() + $report->netEarnings(),
            0.01
        );
    }

    /**
     * The admin's grouped query and the per-merchant report are two separate
     * code paths over the same data. They must agree, or the store and the
     * supplier are reading different books.
     */
    public function test_the_admin_breakdown_agrees_with_each_merchants_own_report(): void
    {
        $perSeller = SalesReport::perSeller();

        $this->assertNotEmpty($perSeller);

        foreach ($perSeller as $row) {
            $theirs = SalesReport::forSeller($row->seller_id);

            $this->assertEqualsWithDelta($theirs->totalEarnings(), $row->gross, 0.01, 'gross disagrees');
            $this->assertEqualsWithDelta($theirs->commission(), $row->commission, 0.01, 'commission disagrees');
            $this->assertEqualsWithDelta($theirs->netEarnings(), $row->net, 0.01, 'net disagrees');
            $this->assertSame($theirs->unitsSold(), $row->units, 'units disagree');
        }
    }

    public function test_the_breakdown_sums_to_the_whole_store(): void
    {
        $perSeller = SalesReport::perSeller();
        $store = new SalesReport;

        $this->assertEqualsWithDelta($store->totalEarnings(), $perSeller->sum('gross'), 0.01);
        $this->assertEqualsWithDelta($store->commission(), $perSeller->sum('commission'), 0.01);
        $this->assertEqualsWithDelta($store->netEarnings(), $perSeller->sum('net'), 0.01);
    }

    public function test_a_cancelled_order_stops_counting_towards_earnings(): void
    {
        $merchant = $this->merchant();
        $before = SalesReport::forSeller($merchant->id)->netEarnings();

        $order = Order::whereHas('items', fn ($q) => $q->where('seller_id', $merchant->id))
            ->where('status', '!=', OrderStatus::Cancelled)
            ->firstOrFail();

        $theirShare = $order->items
            ->where('seller_id', $merchant->id)
            ->sum(fn (OrderItem $item) => $item->net_earnings);

        $order->update(['status' => OrderStatus::Cancelled]);

        $this->assertEqualsWithDelta(
            $before - $theirShare,
            SalesReport::forSeller($merchant->id)->netEarnings(),
            0.01,
            'a cancelled order still counted towards what the merchant earned'
        );
    }

    public function test_changing_the_commission_rate_does_not_rewrite_past_earnings(): void
    {
        $merchant = $this->merchant();
        $before = SalesReport::forSeller($merchant->id)->commission();

        config(['marketplace.commission_rate' => 40]);

        $this->assertEqualsWithDelta(
            $before,
            SalesReport::forSeller($merchant->id)->commission(),
            0.01,
            'past commission followed the new rate'
        );
    }

    public function test_repricing_the_catalog_does_not_move_earnings(): void
    {
        $merchant = $this->merchant();
        $before = SalesReport::forSeller($merchant->id)->totalEarnings();

        Product::query()->update(['price' => 1.00]);

        $this->assertEqualsWithDelta($before, SalesReport::forSeller($merchant->id)->totalEarnings(), 0.01);
    }

    // The admin view

    public function test_the_admin_table_lists_every_supplier_and_what_is_owed(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/earnings')->assertOk();

        foreach (SalesReport::perSeller() as $row) {
            $response->assertSee($row->email, false);
            $response->assertSee(number_format($row->net, 2), false);
        }

        $response->assertSee('Owed to Merchants', false);
    }

    public function test_the_admin_table_is_linked_from_the_sidebar(): void
    {
        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertSee(route('admin.earnings'), false)
            ->assertSee('>Earnings<', false);
    }

    public function test_the_merchant_sidebar_links_to_their_sales(): void
    {
        $this->actingAs($this->merchant())->get('/merchant')
            ->assertOk()
            ->assertSee(route('merchant.sales'), false)
            ->assertSee('>My Sales<', false);
    }
}
