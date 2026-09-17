<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Mail\ContactMessage;
use App\Models\Category;
use App\Models\NewsletterSubscriber;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_the_home_page_renders(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_the_home_page_lists_every_category_with_its_product_count(): void
    {
        $response = $this->get('/');

        foreach (Category::withCount('products')->get() as $category) {
            $response->assertSee($category->name, false);
            $response->assertSee("{$category->products_count} items", false);
        }
    }

    public function test_the_home_page_shows_products(): void
    {
        $product = Product::approved()->latest('id')->first();

        $this->get('/')
            ->assertSee($product->name, false)
            ->assertSee(number_format($product->price, 2), false);
    }

    public function test_products_awaiting_review_stay_off_the_storefront(): void
    {
        $product = Product::approved()->latest('id')->firstOrFail();
        $product->update(['status' => ProductStatus::Pending]);

        $this->get('/')->assertDontSee($product->name, false);
    }

    /**
     * Asset URLs must be absolute.
     *
     * The markup is served from routes at varying depths, so a bare relative
     * path such as `assets/css/style.css` resolves against whatever segment the
     * visitor happens to be on and quietly 404s. Nothing breaks loudly when this
     * regresses; the page simply arrives unstyled.
     */
    public function test_assets_are_referenced_absolutely(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertDoesNotMatchRegularExpression(
            '/(?:src|href)="(?!https?:)\.{0,2}\/?(?:assets|images)\//',
            $html,
            'found a relative asset path; use asset() so it resolves from any route depth'
        );

        $this->assertStringContainsString(url('/assets/css/style.css'), $html);
    }

    public function test_every_asset_the_home_page_asks_for_exists(): void
    {
        $html = $this->get('/')->getContent();

        // Only files under the asset roots; page links are not files on disk.
        preg_match_all(
            '/(?:src|href)="'.preg_quote(url('/'), '/').'\/((?:assets|images)\/[^"?#]+)"/',
            $html,
            $matches
        );

        $this->assertNotEmpty($matches[1], 'no local assets found on the page');

        foreach (array_unique($matches[1]) as $path) {
            $this->assertFileExists(public_path($path));
        }
    }

    public static function staticPages(): array
    {
        return [
            'about' => ['/about', 'Welcome to Meals On Wheels'],
            'privacy policy' => ['/privacy-policy', 'Privacy Policy'],
            'terms' => ['/terms-conditions', 'Introduction'],
            'contact' => ['/contact', 'Get in Touch'],
        ];
    }

    #[DataProvider('staticPages')]
    public function test_a_static_page_renders(string $path, string $expected): void
    {
        $this->get($path)->assertOk()->assertSee($expected, false);
    }

    public function test_an_unknown_url_shows_the_designed_error_page(): void
    {
        $this->get('/no-such-page')
            ->assertNotFound()
            ->assertSee('That link is broken', false);
    }

    public function test_the_contact_form_sends_a_message(): void
    {
        Mail::fake();

        $this->post('/contact', [
            'name' => 'Priya Nair',
            'email' => 'priya@example.test',
            'message' => 'Do you stock grain-free kitten food?',
        ])->assertRedirect()->assertSessionHas('status');

        Mail::assertSent(ContactMessage::class, function (ContactMessage $mail) {
            // Sent from the site address with the visitor as reply-to, so it
            // does not fail SPF at the receiving end.
            return $mail->senderEmail === 'priya@example.test'
                && $mail->envelope()->replyTo[0]->address === 'priya@example.test';
        });
    }

    public function test_the_contact_form_rejects_bad_input(): void
    {
        Mail::fake();

        $this->post('/contact', ['name' => '', 'email' => 'not-an-email', 'message' => ''])
            ->assertSessionHasErrors(['name', 'email', 'message']);

        Mail::assertNothingSent();
    }

    public function test_a_visitor_can_subscribe_to_the_newsletter(): void
    {
        $this->post('/newsletter', ['email' => 'reader@example.test'])
            ->assertRedirect()
            ->assertSessionHas('newsletter');

        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'reader@example.test']);
    }

    public function test_subscribing_twice_is_not_an_error(): void
    {
        $this->post('/newsletter', ['email' => 'reader@example.test']);
        $this->post('/newsletter', ['email' => 'reader@example.test'])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, NewsletterSubscriber::where('email', 'reader@example.test')->count());
    }

    public function test_the_header_reflects_who_is_looking(): void
    {
        $this->get('/')->assertSee('Login', false);

        $customer = User::where('email', 'customer@mealsonwheels.test')->firstOrFail();

        $this->actingAs($customer)->get('/')
            ->assertSee('Hey, '.ucwords($customer->firstname), false)
            ->assertSee('Order History', false);
    }

    public function test_staff_accounts_are_not_offered_a_basket(): void
    {
        $admin = User::where('email', 'admin@mealsonwheels.test')->firstOrFail();

        $this->actingAs($admin)->get('/')->assertDontSee('id="cartItem"', false);
    }
}
