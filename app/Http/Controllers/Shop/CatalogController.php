<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * The whole approved catalog.
     */
    public function index(): View
    {
        return view('shop.index', [
            'heading' => 'Shop',
            'products' => Product::approved()
                ->with('images')
                ->orderBy('name')
                ->paginate(24),
        ]);
    }

    /**
     * One category's products.
     */
    public function category(Category $category): View
    {
        return view('shop.index', [
            'heading' => $category->name,
            'category' => $category,
            'products' => $category->products()
                ->approved()
                ->with('images')
                ->orderBy('name')
                ->paginate(24),
        ]);
    }

    public function product(Product $product): View
    {
        // Route model binding resolves by slug without regard to status, so an
        // unreviewed or rejected listing must not be reachable by guessing it.
        abort_unless($product->isApproved(), 404);

        $product->increment('views');

        return view('shop.product', [
            'product' => $product->load('category', 'images'),
        ]);
    }
}
