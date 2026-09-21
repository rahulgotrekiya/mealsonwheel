<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Listings waiting to be let onto the storefront.
 *
 * This is the gate that keeps the shop to things an admin has actually agreed
 * to sell: a merchant can submit whatever they like, but only an approval puts
 * it in front of customers.
 */
class ProductReviewController extends Controller
{
    public function index(): View
    {
        $this->authorize('review', Product::class);

        return view('admin.products.review', [
            'pending' => Product::where('status', ProductStatus::Pending)
                ->with('category', 'seller', 'images')
                ->oldest('updated_at')
                ->paginate(20),
            'rejected' => Product::where('status', ProductStatus::Rejected)
                ->with('seller')
                ->latest('updated_at')
                ->paginate(20, ['*'], 'rejected'),
        ]);
    }

    public function approve(Product $product): RedirectResponse
    {
        $this->authorize('review', Product::class);

        $product->update([
            'status' => ProductStatus::Approved,
            'reject_reason' => null,
        ]);

        return back()->with('status', "\"{$product->name}\" is now live in the shop.");
    }

    public function reject(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('review', Product::class);

        $data = $request->validate([
            // The supplier is shown this, so it has to say something useful.
            'reject_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $product->update([
            'status' => ProductStatus::Rejected,
            'reject_reason' => $data['reject_reason'],
        ]);

        return back()->with('status', "\"{$product->name}\" was sent back to its supplier.");
    }
}
