<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * What the supplier has sitting in the warehouse.
 *
 * Kept apart from the product form on purpose: restocking is routine and must
 * not send a live listing back for review, whereas changing what a listing
 * says must.
 */
class StockController extends Controller
{
    public function index(Request $request): View
    {
        return view('merchant.stock', [
            'products' => Product::query()
                ->forSeller($request->user()->id)
                ->with('category')
                ->orderBy('stock')
                ->orderBy('name')
                ->paginate(50),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = $request->validate([
            'stock' => ['required', 'integer', 'min:0', 'max:99999'],
        ]);

        // NOTE: suppliers set this figure themselves, on trust. Add a
        // consignment flow — supplier declares, warehouse confirms receipt —
        // if stock accuracy ever matters more than keeping this simple.
        $product->update(['stock' => $data['stock']]);

        return back()->with('status', "Stock for \"{$product->name}\" is now {$product->stock}.");
    }
}
