<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\SalesReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
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

    // Access

    public function test_the_dashboard_needs_an_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');

        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
        $this->actingAs(User::factory()->merchant()->create())->get('/admin')->assertForbidden();
    }

    public function test_the_dashboard_renders_for_an_admin(): void
    {
        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertSee('Total Earnings', false)
            ->assertSee('Revenue, last 10 days', false);
    }

    // The shared panel shell

    public function test_the_sidebar_shows_only_the_signed_in_role_menu(): void
    {
        $this->actingAs($this->admin())->get('/admin')
            ->assertSee('Dashboard', false)
            ->assertSee('Visit Store', false);
    }

    public function test_admin_and_merchant_share_one_panel_layout(): void
    {
        $merchant = User::where('email', 'pawsome@mealsonwheels.test')->firstOrFail();

        $adminHtml = $this->actingAs($this->admin())->get('/admin')->getContent();
        $merchantHtml = $this->actingAs($merchant)->get('/merchant')->getContent();

        foreach (['app-menu navbar-menu', 'page-topbar', 'layout-wrapper', 'assets/panel/css/app.min.css'] as $marker) {
            $this->assertStringContainsString($marker, $adminHtml, "admin panel is missing {$marker}");
            $this->assertStringContainsString($marker, $merchantHtml, "merchant panel is missing {$marker}");
        }
    }

    public function test_each_role_sees_its_own_label_in_the_topbar(): void
    {
        $merchant = User::where('email', 'pawsome@mealsonwheels.test')->firstOrFail();

        $this->actingAs($this->admin())->get('/admin')->assertSee('Admin', false);
        $this->actingAs($merchant)->get('/merchant')->assertSee('Merchant', false);
    }

    public function test_every_panel_asset_exists_on_disk(): void
    {
        $html = $this->actingAs($this->admin())->get('/admin')->getContent();

        preg_match_all('/(?:src|href)="'.preg_quote(url('/'), '/').'\/(assets\/panel\/[^"?#]+)"/', $html, $matches);

        $this->assertNotEmpty($matches[1], 'the panel loaded no local assets');

        foreach (array_unique($matches[1]) as $path) {
            $this->assertFileExists(public_path($path));
        }
    }

    /**
     * A real directory under public/ is served before the router ever runs, so
     * one named after a route silently shadows it. Putting the panel theme in
     * public/admin/ made GET /admin return 404 rather than the dashboard.
     */
    public function test_no_directory_under_public_shadows_a_route(): void
    {
        $topLevelRoutes = collect(app('router')->getRoutes())
            ->map(fn ($route) => strtok($route->uri(), '/'))
            ->reject(fn ($segment) => $segment === '' || str_starts_with($segment, '{'))
            ->unique();

        foreach (glob(public_path('*'), GLOB_ONLYDIR) as $directory) {
            $this->assertNotContains(
                basename($directory),
                $topLevelRoutes,
                'public/'.basename($directory).' shadows the route of the same name'
            );
        }
    }

    // The figures

    public function test_the_figures_come_from_the_price_recorded_at_sale(): void
    {
        $report = new SalesReport;
        $before = $report->totalEarnings();

        $this->assertGreaterThan(0, $before, 'the seeded orders should produce revenue');

        // Repricing the catalog must not move a historical total.
        Product::query()->update(['price' => 1.00]);

        $this->assertSame($before, (new SalesReport)->totalEarnings());
    }

    public function test_cancelled_orders_are_not_counted_as_revenue(): void
    {
        $before = (new SalesReport)->totalEarnings();

        $order = Order::where('status', '!=', OrderStatus::Cancelled)->firstOrFail();
        $lineValue = $order->items->sum(fn ($item) => $item->subtotal);

        $order->update(['status' => OrderStatus::Cancelled]);

        $this->assertEqualsWithDelta(
            $before - $lineValue,
            (new SalesReport)->totalEarnings(),
            0.01,
            'a cancelled order still counted towards revenue'
        );
    }

    public function test_the_revenue_chart_covers_a_full_run_of_days(): void
    {
        $daily = (new SalesReport)->dailyRevenue(10);

        $this->assertCount(10, $daily, 'quiet days should still appear, as zero');
        $this->assertSame(today()->toDateString(), $daily->keys()->last());
        $this->assertTrue($daily->every(fn ($value) => is_float($value)));
    }

    public function test_sales_by_category_totals_the_units_sold(): void
    {
        $byCategory = (new SalesReport)->salesByCategory();

        $this->assertNotEmpty($byCategory);
        $this->assertSame((new SalesReport)->unitsSold(), $byCategory->sum());
    }

    public function test_a_seller_scoped_report_counts_only_that_merchants_lines(): void
    {
        $merchant = User::where('email', 'pawsome@mealsonwheels.test')->firstOrFail();

        $all = (new SalesReport)->totalEarnings();
        $theirs = SalesReport::forSeller($merchant->id)->totalEarnings();

        $this->assertGreaterThan(0, $theirs);
        $this->assertLessThan($all, $theirs, 'one merchant should not account for the whole store');
    }

    public function test_commission_and_net_earnings_split_the_takings(): void
    {
        $report = new SalesReport;

        $this->assertEqualsWithDelta(
            $report->totalEarnings(),
            $report->commission() + $report->netEarnings(),
            0.01
        );
    }
}
