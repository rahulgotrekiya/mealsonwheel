<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The basket page and the endpoints its markup calls.
 *
 * Every mutation goes through the Cart service, which scopes database work to
 * the signed-in user, so a crafted product id can only ever affect the caller's
 * own basket.
 */
class CartController extends Controller
{
    public function __construct(private readonly Cart $cart) {}

    public function index(): View
    {
        return view('shop.cart');
    }

    public function add(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['id']);

        return response()->json($this->cart->add($product, (int) $data['quantity']));
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['required', 'integer'],
            'qty' => ['required', 'integer'],
        ]);

        return response()->json($this->cart->update((int) $data['id'], (int) $data['qty']));
    }

    public function remove(Request $request): JsonResponse
    {
        $data = $request->validate(['id' => ['required', 'integer']]);

        return response()->json($this->cart->remove((int) $data['id']));
    }

    /**
     * Drives the basket count in the header and the mobile toolbar.
     */
    public function fetch(): JsonResponse
    {
        return response()->json([
            'count' => $this->cart->count(),
            'total_quantity' => $this->cart->totalQuantity(),
        ]);
    }

    /**
     * The basket table body, rendered server side.
     *
     * Returns an empty string for an empty basket; the page uses that to swap
     * between the table and the "your cart is empty" panel.
     */
    public function details(): JsonResponse
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            return response()->json('');
        }

        return response()->json(view('shop.partials.cart-rows', [
            'lines' => $lines,
            'total' => $this->cart->total(),
        ])->render());
    }

    public function total(): JsonResponse
    {
        // Answers for signed-out visitors too, so the page does not sit on a
        // failed parse waiting for a number that never arrives.
        return response()->json($this->cart->total());
    }
}
