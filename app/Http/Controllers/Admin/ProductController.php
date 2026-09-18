<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SaveProduct;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly SaveProduct $products) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $search = trim((string) $request->query('search', ''));

        return view('admin.products.index', [
            'search' => $search,
            'products' => Product::query()
                // Left join, so a product never disappears from the list just
                // because of the state of its category.
                ->with('category', 'seller', 'images')
                ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('admin.products.create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->products->create(
            [
                ...$request->safe()->except('images', 'remove_images'),
                // An admin is the store, so what they list needs no review.
                'status' => ProductStatus::Approved,
                'seller_id' => null,
            ],
            $request->file('images', [])
        );

        return redirect()
            ->route('admin.products.index')
            ->with('status', "\"{$product->name}\" has been added.");
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('admin.products.edit', [
            'product' => $product->load('images'),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->products->update(
            $product,
            $request->safe()->except('images', 'remove_images'),
            $request->file('images', []),
            $request->input('remove_images', [])
        );

        return redirect()
            ->route('admin.products.index')
            ->with('status', "\"{$product->name}\" has been updated.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $name = $product->name;

        $this->products->delete($product);

        return redirect()
            ->route('admin.products.index')
            ->with('status', "\"{$name}\" has been removed.");
    }
}
