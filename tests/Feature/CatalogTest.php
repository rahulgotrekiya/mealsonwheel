<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function anyProduct(): Product
    {
        return Product::approved()->firstOrFail();
    }

    // Shop listing

    public function test_the_shop_lists_the_whole_approved_catalog(): void
    {
        $response = $this->get('/shop')->assertOk();

        foreach (Product::approved()->get() as $product) {
            $response->assertSee($product->name, false);
        }
    }

    public function test_the_shop_hides_products_awaiting_review(): void
    {
        $product = $this->anyProduct();
        $product->update(['status' => ProductStatus::Pending]);

        $this->get('/shop')->assertDontSee($product->name, false);
    }

    // Category pages

    public function test_a_category_page_shows_only_its_own_products(): void
    {
        $category = Category::where('slug', 'dog')->firstOrFail();

        $response = $this->get("/category/{$category->slug}")->assertOk();

        foreach ($category->products()->approved()->get() as $product) {
            $response->assertSee($product->name, false);
        }

        $other = Product::approved()->where('category_id', '!=', $category->id)->firstOrFail();
        $response->assertDontSee($other->name, false);
    }

    public function test_an_unknown_category_is_not_found(): void
    {
        $this->get('/category/reptiles')->assertNotFound();
    }

    // Product detail

    public function test_a_product_page_renders_its_details(): void
    {
        $product = $this->anyProduct();

        $this->get("/product/{$product->slug}")
            ->assertOk()
            ->assertSee($product->name, false)
            ->assertSee(number_format($product->price, 2), false)
            ->assertSee($product->category->name, false);
    }

    public function test_a_product_page_shows_every_image_it_has(): void
    {
        $product = Product::approved()->has('images', '>', 1)->with('images')->firstOrFail();

        $response = $this->get("/product/{$product->slug}");

        foreach ($product->images as $image) {
            $response->assertSee(asset($image->path), false);
        }
    }

    public function test_an_unreviewed_product_cannot_be_reached_by_guessing_its_url(): void
    {
        $product = $this->anyProduct();
        $product->update(['status' => ProductStatus::Pending]);

        $this->get("/product/{$product->slug}")->assertNotFound();
    }

    public function test_an_unknown_product_is_not_found(): void
    {
        $this->get('/product/no-such-thing')->assertNotFound();
    }

    public function test_viewing_a_product_counts_the_visit(): void
    {
        $product = $this->anyProduct();
        $before = $product->views;

        $this->get("/product/{$product->slug}");

        $this->assertSame($before + 1, $product->fresh()->views);
    }

    public function test_an_out_of_stock_product_cannot_be_added_to_the_basket(): void
    {
        $product = $this->anyProduct();
        $product->update(['stock' => 0]);

        $this->get("/product/{$product->slug}")
            ->assertOk()
            ->assertSee('Out of stock', false)
            ->assertSee('disabled', false);
    }

    public function test_staff_are_not_shown_an_add_to_basket_form(): void
    {
        $admin = User::where('email', 'admin@mealsonwheels.test')->firstOrFail();

        $this->actingAs($admin)
            ->get("/product/{$this->anyProduct()->slug}")
            ->assertDontSee('id="productForm"', false);
    }

    // Search

    public function test_searching_finds_matching_products(): void
    {
        $this->get('/search?keyword=Boltz')
            ->assertOk()
            ->assertSee('Boltz', false)
            ->assertDontSee('Woof Treats Organic Dog Snacks', false);
    }

    public function test_the_search_keyword_is_highlighted_in_results(): void
    {
        $this->get('/search?keyword=Boltz')->assertSee('<b>Boltz</b>', false);
    }

    public function test_a_search_with_no_matches_says_so(): void
    {
        $this->get('/search?keyword=zzzznothing')
            ->assertOk()
            ->assertSee('No Results Found For: zzzznothing', false);
    }

    public function test_a_search_keyword_cannot_inject_markup(): void
    {
        $this->get('/search?keyword='.urlencode('<script>alert(1)</script>'))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_results_can_be_sorted_by_price(): void
    {
        $ascending = $this->get('/search?keyword=&sort=price_asc')->getContent();
        $descending = $this->get('/search?keyword=&sort=price_desc')->getContent();

        $cheapest = Product::approved()->orderBy('price')->first();
        $dearest = Product::approved()->orderByDesc('price')->first();

        $this->assertLessThan(
            strpos($ascending, $dearest->name),
            strpos($ascending, $cheapest->name),
            'ascending sort did not put the cheapest product first'
        );
        $this->assertLessThan(
            strpos($descending, $cheapest->name),
            strpos($descending, $dearest->name),
            'descending sort did not put the dearest product first'
        );
    }

    public function test_an_unrecognised_sort_falls_back_instead_of_reaching_the_query(): void
    {
        $this->get('/search?keyword=&sort='.urlencode('price); DROP TABLE products;--'))
            ->assertOk();

        $this->assertSame(19, Product::count(), 'the products table did not survive');
    }

    public function test_results_can_be_filtered_by_price(): void
    {
        $cheap = Product::approved()->orderBy('price')->first();
        $dear = Product::approved()->orderByDesc('price')->first();

        $this->get('/search?keyword=&min_price=0&max_price='.((int) $cheap->price))
            ->assertOk()
            ->assertSee($cheap->name, false)
            ->assertDontSee($dear->name, false);
    }

    public function test_a_reversed_price_range_still_returns_results(): void
    {
        // Min above max would otherwise match nothing at all.
        $this->get('/search?keyword=&min_price=5000&max_price=0')
            ->assertOk()
            ->assertDontSee('No Results Found', false);
    }

    public function test_filters_survive_across_pages(): void
    {
        $this->get('/search?keyword=Boltz&sort=price_desc')
            ->assertOk()
            ->assertSee('value="Boltz"', false);
    }

    // Links

    public function test_no_page_links_to_a_script_file(): void
    {
        // Routes are named paths, so a link ending in .php is a dead one that
        // would 404 without anything else failing first.
        foreach (['/', '/shop', '/about', '/contact', '/privacy-policy', '/terms-conditions'] as $path) {
            $this->assertDoesNotMatchRegularExpression(
                '/(?:href|action)="[^"]*\.php["?]/',
                $this->get($path)->getContent(),
                "{$path} links to a script file"
            );
        }
    }

    public function test_the_navigation_links_to_pages_that_exist(): void
    {
        $html = $this->get('/')->getContent();

        preg_match_all('/href="'.preg_quote(url('/'), '/').'(\/[^"#?]*)"/', $html, $matches);

        // Sections still being built. Each is asserted to be absent below, so
        // this list cannot quietly go stale as they arrive.
        $notYetBuilt = [];

        $checked = 0;

        foreach (array_unique($matches[1]) as $path) {
            if (str_starts_with($path, '/assets') || str_starts_with($path, '/images')) {
                continue;
            }

            if (in_array($path, $notYetBuilt, true)) {
                continue;
            }

            $status = $this->get($path)->getStatusCode();
            $this->assertContains($status, [200, 302], "{$path} returned {$status}");
            $checked++;
        }

        $this->assertGreaterThan(5, $checked, 'expected the page to link somewhere');

        foreach ($notYetBuilt as $path) {
            $this->assertSame(
                404,
                $this->get($path)->getStatusCode(),
                "{$path} now exists; drop it from the pending list in this test"
            );
        }
    }
}
