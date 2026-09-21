<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\PanelMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MerchantCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('products');
    }

    private function admin(): User
    {
        return User::where('email', 'admin@mealsonwheels.test')->firstOrFail();
    }

    private function merchant(): User
    {
        return User::where('email', 'pawsome@mealsonwheels.test')->firstOrFail();
    }

    private function otherMerchant(): User
    {
        return User::where('email', 'whiskers@mealsonwheels.test')->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function listing(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Rawhide Twists Large',
            'category_id' => Category::where('slug', 'dog')->value('id'),
            'price' => '275.00',
            'stock' => '40',
            'description' => '<p>Long lasting chews.</p>',
            'additional_info' => '<p>One a day.</p>',
        ], $overrides);
    }

    // Access

    public function test_the_merchant_screens_need_a_merchant(): void
    {
        $this->get('/merchant/products')->assertRedirect('/login');
        $this->get('/merchant/stock')->assertRedirect('/login');
    }

    public function test_a_customer_cannot_reach_the_merchant_screens(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/merchant/products')->assertForbidden();
        $this->get('/merchant/stock')->assertForbidden();
    }

    public function test_a_merchant_cannot_reach_the_review_queue(): void
    {
        $this->actingAs($this->merchant())->get('/admin/reviews')->assertForbidden();
    }

    // Listing

    public function test_a_merchant_sees_only_their_own_products(): void
    {
        $mine = $this->merchant()->products()->firstOrFail();
        $theirs = $this->otherMerchant()->products()->firstOrFail();

        $this->actingAs($this->merchant())->get('/merchant/products')
            ->assertOk()
            ->assertSee($mine->name, false)
            ->assertDontSee($theirs->name, false);
    }

    // Submitting

    public function test_a_new_listing_is_held_for_review_and_stays_out_of_the_shop(): void
    {
        $this->actingAs($this->merchant())
            ->post('/merchant/products', $this->listing())
            ->assertRedirect('/merchant/products');

        $product = Product::where('name', 'Rawhide Twists Large')->firstOrFail();

        $this->assertSame(ProductStatus::Pending, $product->status);
        $this->assertSame($this->merchant()->id, $product->seller_id);

        // Not in the shop, and not reachable by guessing its address either.
        $this->get('/shop')->assertDontSee($product->name, false);
        $this->get("/product/{$product->slug}")->assertNotFound();
    }

    public function test_a_merchant_cannot_publish_their_own_listing(): void
    {
        // A crafted status field must not bypass review.
        $this->actingAs($this->merchant())
            ->post('/merchant/products', $this->listing(['status' => ProductStatus::Approved->value]));

        $this->assertSame(
            ProductStatus::Pending,
            Product::where('name', 'Rawhide Twists Large')->firstOrFail()->status
        );
    }

    public function test_a_merchant_cannot_submit_a_listing_for_someone_else(): void
    {
        $this->actingAs($this->merchant())
            ->post('/merchant/products', $this->listing(['seller_id' => $this->otherMerchant()->id]));

        $this->assertSame(
            $this->merchant()->id,
            Product::where('name', 'Rawhide Twists Large')->firstOrFail()->seller_id
        );
    }

    public function test_a_listing_can_carry_images(): void
    {
        $this->actingAs($this->merchant())->post('/merchant/products', $this->listing([
            'images' => [UploadedFile::fake()->image('twists.jpg')],
        ]));

        $product = Product::where('name', 'Rawhide Twists Large')->firstOrFail();

        $this->assertCount(1, $product->images);
        Storage::disk('products')->assertExists(basename($product->images->first()->path));
    }

    // Ownership

    public function test_a_merchant_cannot_open_or_change_another_suppliers_listing(): void
    {
        $theirs = $this->otherMerchant()->products()->firstOrFail();

        $this->actingAs($this->merchant())->get("/merchant/products/{$theirs->slug}/edit")->assertForbidden();
        $this->actingAs($this->merchant())->put("/merchant/products/{$theirs->slug}", $this->listing())->assertForbidden();
        $this->actingAs($this->merchant())->delete("/merchant/products/{$theirs->slug}")->assertForbidden();

        $this->assertNotSoftDeleted('products', ['id' => $theirs->id]);
        $this->assertSame('Jomo Salmon and Sweet Potato Dry Cat Food 400g', $theirs->fresh()->name);
    }

    // Editing sends it back for review

    public function test_editing_a_live_listing_sends_it_back_for_review(): void
    {
        $product = $this->merchant()->products()->where('status', ProductStatus::Approved)->firstOrFail();

        $this->actingAs($this->merchant())
            ->put("/merchant/products/{$product->slug}", $this->listing(['name' => $product->name]))
            ->assertRedirect('/merchant/products');

        $product->refresh();

        $this->assertSame(ProductStatus::Pending, $product->status);
        $this->get('/shop')->assertDontSee($product->name, false);
    }

    public function test_resubmitting_clears_the_previous_rejection_note(): void
    {
        $product = $this->merchant()->products()->firstOrFail();
        $product->update(['status' => ProductStatus::Rejected, 'reject_reason' => 'Photo is too dark.']);

        $this->actingAs($this->merchant())
            ->put("/merchant/products/{$product->slug}", $this->listing(['name' => $product->name]));

        $this->assertNull($product->fresh()->reject_reason);
    }

    // Stock is a separate concern

    public function test_changing_stock_does_not_send_a_listing_back_for_review(): void
    {
        $product = $this->merchant()->products()->where('status', ProductStatus::Approved)->firstOrFail();

        $this->actingAs($this->merchant())
            ->patch("/merchant/stock/{$product->slug}", ['stock' => 99])
            ->assertRedirect();

        $product->refresh();

        $this->assertSame(99, $product->stock);
        // Restocking is routine; it must not unpublish a live product.
        $this->assertSame(ProductStatus::Approved, $product->status);
        $this->get('/shop')->assertSee($product->name, false);
    }

    public function test_stock_cannot_be_set_to_something_nonsensical(): void
    {
        $product = $this->merchant()->products()->firstOrFail();
        $before = $product->stock;

        $this->actingAs($this->merchant())
            ->patch("/merchant/stock/{$product->slug}", ['stock' => -5])
            ->assertSessionHasErrors('stock');

        $this->assertSame($before, $product->fresh()->stock);
    }

    public function test_a_merchant_cannot_restock_another_suppliers_product(): void
    {
        $theirs = $this->otherMerchant()->products()->firstOrFail();
        $before = $theirs->stock;

        $this->actingAs($this->merchant())
            ->patch("/merchant/stock/{$theirs->slug}", ['stock' => 999])
            ->assertForbidden();

        $this->assertSame($before, $theirs->fresh()->stock);
    }

    public function test_the_stock_page_lists_the_emptiest_first(): void
    {
        $merchant = $this->merchant();
        $merchant->products()->firstOrFail()->update(['stock' => 1]);

        $html = $this->actingAs($merchant)->get('/merchant/stock')->assertOk()->getContent();

        $lowest = $merchant->products()->orderBy('stock')->first();
        $highest = $merchant->products()->orderByDesc('stock')->first();

        $this->assertLessThan(
            strpos($html, $highest->name),
            strpos($html, $lowest->name),
            'the emptiest product should be at the top'
        );
    }

    // Review

    public function test_the_queue_lists_what_is_waiting(): void
    {
        $this->actingAs($this->merchant())->post('/merchant/products', $this->listing());

        $this->actingAs($this->admin())->get('/admin/reviews')
            ->assertOk()
            ->assertSee('Rawhide Twists Large', false)
            ->assertSee('1 waiting', false);
    }

    /**
     * The sidebar counts what is waiting.
     *
     * Asserted against the menu itself rather than the rendered page: the
     * merchant-approvals entry carries a badge of its own, so counting badges
     * in the HTML could not tell the two apart.
     */
    public function test_the_review_menu_counts_listings_waiting(): void
    {
        $badge = fn () => collect(PanelMenu::for($this->admin()))
            ->firstWhere('label', 'Reviews')['badge'];

        $this->assertSame(0, $badge());

        $this->actingAs($this->merchant())->post('/merchant/products', $this->listing());

        $this->assertSame(1, $badge());
    }

    public function test_the_review_queue_is_linked_from_the_sidebar(): void
    {
        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertSee(route('admin.reviews.index'), false)
            ->assertSee('>Reviews<', false);
    }

    public function test_approving_puts_a_listing_in_the_shop(): void
    {
        $this->actingAs($this->merchant())->post('/merchant/products', $this->listing());
        $product = Product::where('name', 'Rawhide Twists Large')->firstOrFail();

        $this->actingAs($this->admin())
            ->patch("/admin/reviews/{$product->slug}/approve")
            ->assertRedirect();

        $this->assertSame(ProductStatus::Approved, $product->fresh()->status);

        $this->get('/shop')->assertSee($product->name, false);
        $this->get("/product/{$product->slug}")->assertOk();
    }

    public function test_sending_a_listing_back_requires_a_reason_the_supplier_can_act_on(): void
    {
        $this->actingAs($this->merchant())->post('/merchant/products', $this->listing());
        $product = Product::where('name', 'Rawhide Twists Large')->firstOrFail();

        $this->actingAs($this->admin())
            ->patch("/admin/reviews/{$product->slug}/reject", ['reject_reason' => ''])
            ->assertSessionHasErrors('reject_reason');

        $this->assertSame(ProductStatus::Pending, $product->fresh()->status);
    }

    public function test_a_rejected_listing_shows_its_reason_to_the_supplier(): void
    {
        $this->actingAs($this->merchant())->post('/merchant/products', $this->listing());
        $product = Product::where('name', 'Rawhide Twists Large')->firstOrFail();

        $this->actingAs($this->admin())->patch("/admin/reviews/{$product->slug}/reject", [
            'reject_reason' => 'Please add a clearer photo of the packaging.',
        ]);

        $this->assertSame(ProductStatus::Rejected, $product->fresh()->status);

        $this->actingAs($this->merchant())->get('/merchant/products')
            ->assertSee('Please add a clearer photo of the packaging.', false);

        $this->get('/shop')->assertDontSee($product->name, false);
    }

    public function test_a_merchant_cannot_approve_anything(): void
    {
        $this->actingAs($this->merchant())->post('/merchant/products', $this->listing());
        $product = Product::where('name', 'Rawhide Twists Large')->firstOrFail();

        $this->actingAs($this->merchant())
            ->patch("/admin/reviews/{$product->slug}/approve")
            ->assertForbidden();

        $this->assertSame(ProductStatus::Pending, $product->fresh()->status);
    }
}
