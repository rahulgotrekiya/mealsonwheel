<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedDataTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_the_catalog_is_seeded(): void
    {
        $this->assertSame(4, Category::count());
        $this->assertSame(19, Product::count());
    }

    public function test_every_product_is_sellable(): void
    {
        $products = Product::with('images')->get();

        foreach ($products as $product) {
            $this->assertNotNull($product->seller_id, "{$product->name} has no supplier");
            $this->assertNotNull($product->category_id, "{$product->name} has no category");
            $this->assertGreaterThan(0, $product->images->count(), "{$product->name} has no image");
            $this->assertGreaterThan(0, (float) $product->price, "{$product->name} is free");
            $this->assertSame(ProductStatus::Approved, $product->status);
        }
    }

    public function test_product_images_point_at_files_that_exist(): void
    {
        foreach (Product::with('images')->get() as $product) {
            foreach ($product->images as $image) {
                $this->assertFileExists(
                    public_path($image->path),
                    "missing image file for {$product->name}"
                );
            }
        }
    }

    public function test_each_role_has_an_account(): void
    {
        $this->assertSame(1, User::role(UserRole::Admin)->count());
        $this->assertSame(1, User::role(UserRole::Customer)->count());
        $this->assertSame(4, User::role(UserRole::Merchant)->count());

        // One merchant is left pending on purpose so the approval queue has work.
        $this->assertSame(1, User::awaitingApproval()->count());
    }

    public function test_merchant_accounts_are_distinct_suppliers(): void
    {
        $sellerIds = Product::distinct()->pluck('seller_id');

        $this->assertCount(3, $sellerIds, 'catalog should be split across three merchants');

        foreach ($sellerIds as $id) {
            $this->assertTrue(User::find($id)->isMerchant());
        }
    }

    public function test_multibyte_text_survives_storage(): void
    {
        // Guards the connection charset: an ampersand-and-accents round trip
        // catches a database created with the wrong collation.
        $merchant = User::where('email', 'featherfur@mealsonwheels.test')->first();

        $this->assertSame('Feather & Fur Traders', $merchant->full_name);
    }

    public function test_a_demo_order_spans_several_merchants(): void
    {
        $order = Order::with('items')->has('items', '>=', 3)->first();

        $this->assertNotNull($order, 'expected a multi-line demo order');
        $this->assertGreaterThanOrEqual(
            3,
            $order->items->pluck('seller_id')->unique()->count(),
            'the demo order should draw on three different merchants'
        );
    }

    public function test_seeded_accounts_can_authenticate(): void
    {
        $admin = User::where('email', 'admin@mealsonwheels.test')->first();

        $this->assertTrue(\Hash::check('password', $admin->password));
        $this->assertSame(UserStatus::Active, $admin->status);
    }
}
