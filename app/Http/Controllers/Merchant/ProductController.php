<?php

namespace App\Http\Controllers\Merchant;

use App\Actions\SaveProduct;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * A merchant's own listings.
 *
 * Every query is scoped to the signed-in supplier and every single-product
 * action goes through the product policy, so one merchant can neither see nor
 * touch another's listing by guessing a slug.
 */
class ProductController extends Controller
{
    public function __construct(private readonly SaveProduct $products) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('merchant.products.index', [
            'search' => $search,
            'products' => Product::query()
                ->forSeller($request->user()->id)
                ->with('category', 'images')
                ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('merchant.products.create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->products->create(
            [
                ...$request->safe()->except('images', 'remove_images'),
                'seller_id' => $request->user()->id,
                // A supplier's listing is reviewed before it reaches the shop.
                'status' => ProductStatus::Pending,
            ],
            $request->file('images', [])
        );

        return redirect()
            ->route('merchant.products.index')
            ->with('status', "\"{$product->name}\" has been submitted for review.");
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('merchant.products.edit', [
            'product' => $product->load('images'),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        /*
         * Changing what a listing says sends it back for review, and clears any
         * previous rejection note — otherwise an approved product could be
         * edited into something nobody agreed to.
         *
         * Stock is deliberately not part of this form: restocking is a separate
         * action that leaves a live product live.
         */
        $this->products->update(
            $product,
            [
                ...$request->safe()->except('images', 'remove_images', 'stock'),
                'status' => ProductStatus::Pending,
                'reject_reason' => null,
            ],
            $request->file('images', []),
            $request->input('remove_images', [])
        );

        return redirect()
            ->route('merchant.products.index')
            ->with('status', "\"{$product->name}\" has been updated and sent for review.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $name = $product->name;

        $this->products->delete($product);

        return redirect()
            ->route('merchant.products.index')
            ->with('status', "\"{$name}\" has been removed.");
    }
}
