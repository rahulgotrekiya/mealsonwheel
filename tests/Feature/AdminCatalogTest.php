<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        // Uploads go to a fake disk so the real catalog images are untouched.
        Storage::fake('products');
        Storage::fake('categories');
    }

    private function admin(): User
    {
        return User::where('email', 'admin@mealsonwheels.test')->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function productPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Salmon Crunch Cat Biscuits',
            'category_id' => Category::where('slug', 'cat')->value('id'),
            'price' => '449.50',
            'stock' => '30',
            'description' => '<p>Crunchy salmon biscuits.</p>',
            'additional_info' => '<p>Feed sparingly.</p>',
        ], $overrides);
    }

    // Access

    public function test_a_guest_is_sent_to_sign_in(): void
    {
        $this->get('/admin/products')->assertRedirect('/login');
        $this->get('/admin/categories')->assertRedirect('/login');
    }

    public function test_a_customer_cannot_reach_the_catalog_screens(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/products')->assertForbidden();
        $this->get('/admin/categories')->assertForbidden();
    }

    public function test_a_merchant_cannot_reach_the_admin_catalog_screens(): void
    {
        $this->actingAs(User::factory()->merchant()->create());

        $this->get('/admin/products')->assertForbidden();
        $this->get('/admin/categories')->assertForbidden();
    }

    public function test_the_product_list_renders(): void
    {
        $this->actingAs($this->admin())->get('/admin/products')
            ->assertOk()
            ->assertSee('Woof Treats Organic Dog Snacks 150g', false);
    }

    public function test_the_product_list_can_be_searched(): void
    {
        $this->actingAs($this->admin())->get('/admin/products?search=Boltz')
            ->assertOk()
            ->assertSee('Boltz', false)
            ->assertDontSee('Woof Treats Organic Dog Snacks 150g', false);
    }

    // Creating

    public function test_an_admin_can_add_a_product(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/products', $this->productPayload())
            ->assertRedirect('/admin/products');

        $product = Product::where('name', 'Salmon Crunch Cat Biscuits')->first();

        $this->assertNotNull($product);
        $this->assertSame('salmon-crunch-cat-biscuits', $product->slug);
        $this->assertSame(30, $product->stock);
        // Listed by the store itself, so it needs no review.
        $this->assertSame(ProductStatus::Approved, $product->status);
        $this->assertNull($product->seller_id);
    }

    public function test_a_duplicate_name_still_gets_a_usable_address(): void
    {
        $existing = Product::approved()->firstOrFail();

        $this->actingAs($this->admin())
            ->post('/admin/products', $this->productPayload(['name' => $existing->name]));

        $created = Product::where('name', $existing->name)->where('id', '!=', $existing->id)->firstOrFail();

        $this->assertNotSame($existing->slug, $created->slug);
        $this->assertSame($existing->slug.'-1', $created->slug);
    }

    public function test_a_product_requires_its_details(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/products', ['name' => '', 'price' => 'free', 'stock' => -5])
            ->assertSessionHasErrors(['name', 'category_id', 'price', 'stock', 'description']);
    }

    // Uploads

    public function test_uploaded_images_are_stored_and_linked(): void
    {
        $this->actingAs($this->admin())->post('/admin/products', $this->productPayload([
            'images' => [
                UploadedFile::fake()->image('front.jpg'),
                UploadedFile::fake()->image('back.png'),
            ],
        ]));

        $product = Product::where('name', 'Salmon Crunch Cat Biscuits')->firstOrFail();

        $this->assertCount(2, $product->images);

        foreach ($product->images as $image) {
            $this->assertStringStartsWith('images/products/', $image->path);
            Storage::disk('products')->assertExists(basename($image->path));
        }
    }

    public function test_the_stored_filename_is_never_the_one_that_was_uploaded(): void
    {
        $this->actingAs($this->admin())->post('/admin/products', $this->productPayload([
            'images' => [UploadedFile::fake()->image('shell.php.jpg')],
        ]));

        $image = Product::where('name', 'Salmon Crunch Cat Biscuits')->firstOrFail()->images->first();

        $this->assertStringNotContainsString('shell', $image->path);
        $this->assertStringNotContainsString('.php', $image->path);
        $this->assertMatchesRegularExpression('#^images/products/[0-9a-f-]{36}\.jpg$#', $image->path);
    }

    public function test_a_non_image_upload_is_refused(): void
    {
        $this->actingAs($this->admin())->post('/admin/products', $this->productPayload([
            'images' => [UploadedFile::fake()->create('payload.php', 16, 'application/x-php')],
        ]))->assertSessionHasErrors('images.0');

        $this->assertDatabaseMissing('products', ['name' => 'Salmon Crunch Cat Biscuits']);
    }

    public function test_an_oversized_image_is_refused(): void
    {
        $this->actingAs($this->admin())->post('/admin/products', $this->productPayload([
            'images' => [UploadedFile::fake()->image('huge.jpg')->size(4096)],
        ]))->assertSessionHasErrors('images.0');
    }

    // Editing

    public function test_an_admin_can_edit_a_product(): void
    {
        $product = Product::approved()->firstOrFail();

        $this->actingAs($this->admin())
            ->put("/admin/products/{$product->slug}", $this->productPayload([
                'name' => $product->name,
                'price' => '123.45',
                'stock' => '7',
            ]))
            ->assertRedirect('/admin/products');

        $product->refresh();

        $this->assertSame('123.45', $product->price);
        $this->assertSame(7, $product->stock);
    }

    public function test_the_address_only_changes_when_the_name_does(): void
    {
        $product = Product::approved()->firstOrFail();
        $slug = $product->slug;

        $this->actingAs($this->admin())
            ->put("/admin/products/{$slug}", $this->productPayload(['name' => $product->name]));

        $this->assertSame($slug, $product->fresh()->slug, 'an unrelated edit moved the product');

        $this->actingAs($this->admin())
            ->put("/admin/products/{$slug}", $this->productPayload(['name' => 'Something Entirely New']));

        $this->assertSame('something-entirely-new', $product->fresh()->slug);
    }

    public function test_images_can_be_removed_on_edit(): void
    {
        $product = Product::approved()->has('images')->with('images')->firstOrFail();
        $image = $product->images->first();

        Storage::disk('products')->put(basename($image->path), 'x');

        $this->actingAs($this->admin())->put("/admin/products/{$product->slug}", $this->productPayload([
            'name' => $product->name,
            'remove_images' => [$image->id],
        ]));

        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
        Storage::disk('products')->assertMissing(basename($image->path));
    }

    // Deleting

    public function test_deleting_a_product_removes_its_images_but_keeps_order_history(): void
    {
        $product = Product::approved()->has('orderItems')->with('images')->firstOrFail();
        $itemCount = $product->orderItems()->count();

        foreach ($product->images as $image) {
            Storage::disk('products')->put(basename($image->path), 'x');
        }

        $this->actingAs($this->admin())
            ->delete("/admin/products/{$product->slug}")
            ->assertRedirect('/admin/products');

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertSame(0, ProductImage::where('product_id', $product->id)->count());

        // The order lines that reference it must survive, or past orders break.
        $this->assertSame($itemCount, $product->orderItems()->count());
    }

    public function test_a_deleted_product_leaves_the_storefront(): void
    {
        $product = Product::approved()->firstOrFail();

        $this->actingAs($this->admin())->delete("/admin/products/{$product->slug}");

        $this->get('/shop')->assertDontSee($product->name, false);
        $this->get("/product/{$product->slug}")->assertNotFound();
    }

    // Categories

    public function test_an_admin_can_add_a_category(): void
    {
        $this->actingAs($this->admin())->post('/admin/categories', [
            'name' => 'Reptiles',
            'slug' => 'reptiles',
            'image' => UploadedFile::fake()->image('reptiles.jpg'),
        ])->assertRedirect('/admin/categories');

        $category = Category::where('slug', 'reptiles')->firstOrFail();

        $this->assertStringStartsWith('images/categories/', $category->image);
        Storage::disk('categories')->assertExists(basename($category->image));
    }

    public function test_a_category_address_must_be_unique(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/categories', ['name' => 'Dogs Again', 'slug' => 'dog'])
            ->assertSessionHasErrors('slug');
    }

    public function test_a_category_keeps_its_image_when_none_is_uploaded(): void
    {
        $category = Category::where('slug', 'dog')->firstOrFail();
        $image = $category->image;

        $this->actingAs($this->admin())
            ->put("/admin/categories/{$category->slug}", ['name' => 'Dogs', 'slug' => 'dog']);

        $this->assertSame($image, $category->fresh()->image);
    }

    public function test_a_category_still_holding_products_cannot_be_deleted(): void
    {
        $category = Category::where('slug', 'dog')->firstOrFail();

        $this->actingAs($this->admin())
            ->delete("/admin/categories/{$category->slug}")
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_an_empty_category_can_be_deleted(): void
    {
        $category = Category::create(['name' => 'Reptiles', 'slug' => 'reptiles']);

        $this->actingAs($this->admin())
            ->delete("/admin/categories/{$category->slug}")
            ->assertRedirect('/admin/categories');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    // Ownership, which the merchant panel will lean on

    public function test_a_merchant_cannot_reach_another_suppliers_product(): void
    {
        $product = Product::approved()->whereNotNull('seller_id')->firstOrFail();
        $otherMerchant = User::factory()->merchant()->create();

        $this->actingAs($otherMerchant)->get("/admin/products/{$product->slug}/edit")->assertForbidden();
        $this->actingAs($otherMerchant)->delete("/admin/products/{$product->slug}")->assertForbidden();

        $this->assertNotSoftDeleted('products', ['id' => $product->id]);
    }
}
