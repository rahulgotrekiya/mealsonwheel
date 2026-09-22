<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use App\Mail\MerchantApplicationReviewed;
use App\Mail\OrderCancellation;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\User;
use App\Support\SalesReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Smalot\PdfParser\Parser;
use Tests\TestCase;

class DocumentTest extends TestCase
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

    /**
     * Read the text back out of a generated document.
     *
     * A status code only proves something was returned; the point of these
     * tests is that the right figures reached the page.
     */
    private function textOf(string $pdf): string
    {
        return (new Parser)->parseContent($pdf)->getText();
    }

    // Invoice

    public function test_a_customer_can_download_their_own_invoice(): void
    {
        $order = $this->customer()->orders()->with('items.product')->firstOrFail();

        $response = $this->actingAs($this->customer())
            ->get(route('orders.invoice', $order))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $text = $this->textOf($response->getContent());

        $this->assertStringContainsString('Invoice', $text);
        $this->assertStringContainsString($order->transaction_id, $text);
        $this->assertStringContainsString($this->customer()->full_name, $text);

        foreach ($order->items as $item) {
            $this->assertStringContainsString($item->product->name, $text);
        }
    }

    public function test_an_invoice_shows_the_price_that_was_paid(): void
    {
        $order = $this->customer()->orders()->with('items.product')->firstOrFail();
        $item = $order->items->first();
        $paid = number_format($item->unit_price, 2);

        $item->product->update(['price' => 7777.00]);

        $text = $this->textOf(
            $this->actingAs($this->customer())->get(route('orders.invoice', $order))->getContent()
        );

        $this->assertStringContainsString($paid, $text);
        $this->assertStringNotContainsString('7,777.00', $text);
    }

    public function test_the_rupee_sign_renders_rather_than_dropping_out(): void
    {
        $order = $this->customer()->orders()->firstOrFail();

        $text = $this->textOf(
            $this->actingAs($this->customer())->get(route('orders.invoice', $order))->getContent()
        );

        // The bundled font must carry U+20B9, or every amount loses its symbol.
        $this->assertStringContainsString("\u{20B9}", $text);
    }

    public function test_one_customer_cannot_download_another_customers_invoice(): void
    {
        $order = $this->customer()->orders()->firstOrFail();

        $this->actingAs(User::factory()->create())
            ->get(route('orders.invoice', $order))
            ->assertForbidden();
    }

    public function test_an_invoice_is_not_public(): void
    {
        $order = $this->customer()->orders()->firstOrFail();

        $this->get(route('orders.invoice', $order))->assertRedirect('/login');
    }

    // Admin reports

    public static function reports(): array
    {
        return [
            'orders' => ['admin.reports.orders'],
            'users' => ['admin.reports.users'],
            'earnings' => ['admin.reports.earnings'],
        ];
    }

    #[DataProvider('reports')]
    public function test_a_report_needs_an_admin(string $route): void
    {
        $this->get(route($route))->assertRedirect('/login');
    }

    #[DataProvider('reports')]
    public function test_a_merchant_cannot_download_a_report(string $route): void
    {
        $this->actingAs(User::factory()->merchant()->create())->get(route($route))->assertForbidden();
    }

    public function test_the_orders_report_lists_every_order_and_totals_them(): void
    {
        $text = $this->textOf(
            $this->actingAs($this->admin())->get(route('admin.reports.orders'))->assertOk()->getContent()
        );

        foreach (Order::all() as $order) {
            $this->assertStringContainsString($order->transaction_id, $text);
        }

        $this->assertStringContainsString(number_format(Order::sum('total_amount'), 2), $text);
    }

    public function test_the_accounts_report_covers_every_role_and_leaks_no_credentials(): void
    {
        $text = $this->textOf(
            $this->actingAs($this->admin())->get(route('admin.reports.users'))->assertOk()->getContent()
        );

        foreach (['Admin', 'Customer', 'Merchant'] as $role) {
            $this->assertStringContainsString($role, $text);
        }

        $this->assertStringContainsString($this->customer()->email, $text);

        // An export is a file that leaves the building; no password material
        // may travel with it.
        $this->assertStringNotContainsString('$2y$', $text);
        $this->assertStringNotContainsString($this->customer()->password, $text);
    }

    public function test_the_earnings_report_matches_the_screen(): void
    {
        $text = $this->textOf(
            $this->actingAs($this->admin())->get(route('admin.reports.earnings'))->assertOk()->getContent()
        );

        $perSeller = SalesReport::perSeller();

        $this->assertNotEmpty($perSeller);

        foreach ($perSeller as $row) {
            $this->assertStringContainsString($row->email, $text);
            $this->assertStringContainsString(number_format($row->net, 2), $text);
        }

        $this->assertStringContainsString(number_format($perSeller->sum('net'), 2), $text);
    }

    // Mail

    public function test_every_mail_template_renders(): void
    {
        $order = $this->customer()->orders()->with('items.product', 'user')->firstOrFail();
        $merchant = User::where('email', 'pawsome@mealsonwheels.test')->firstOrFail();

        $mailables = [
            new OrderConfirmation($order),
            new OrderCancellation($order),
            new ContactMessage('Priya Nair', 'priya@example.test', 'Do you stock kitten food?'),
            new MerchantApplicationReviewed($merchant, approved: true),
            new MerchantApplicationReviewed($merchant, approved: false),
        ];

        foreach ($mailables as $mailable) {
            $rendered = $mailable->render();

            $this->assertNotEmpty($rendered);
            $this->assertStringContainsString('Meals on Wheels', $rendered);
        }
    }

    public function test_the_confirmation_email_itemises_the_order(): void
    {
        $order = $this->customer()->orders()->with('items.product')->firstOrFail();

        $rendered = (new OrderConfirmation($order))->render();

        $this->assertStringContainsString($order->transaction_id, $rendered);

        foreach ($order->items as $item) {
            $this->assertStringContainsString($item->product->name, $rendered);
            $this->assertStringContainsString(number_format($item->unit_price, 2), $rendered);
        }
    }
}
