<?php

namespace App\Support;

use App\Models\Cart as CartLine;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * The basket, wherever it happens to live.
 *
 * A signed-out visitor's basket is kept in the session so they can shop before
 * deciding to register; a signed-in customer's lives in `carts` so it survives
 * logging out and moving device. Both are addressed by product id, and callers
 * do not need to know which store is in use.
 */
class Cart
{
    private const SESSION_KEY = 'cart';

    /**
     * Product id => quantity, for the guest basket.
     *
     * @return array<int, int>
     */
    private function sessionItems(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    /**
     * @param  array<int, int>  $items
     */
    private function putSessionItems(array $items): void
    {
        Session::put(self::SESSION_KEY, $items);
    }

    private function maxPerItem(): int
    {
        return (int) config('marketplace.max_quantity_per_item');
    }

    /**
     * The basket as product/quantity pairs, ready to render or total up.
     *
     * @return Collection<int, array{product: Product, quantity: int, subtotal: float}>
     */
    public function lines(): Collection
    {
        if ($user = Auth::user()) {
            return CartLine::query()
                ->where('user_id', $user->id)
                ->with('product.category', 'product.images')
                ->get()
                ->filter(fn (CartLine $line) => $line->product !== null)
                ->map(fn (CartLine $line) => $this->line($line->product, $line->quantity))
                ->values();
        }

        $quantities = $this->sessionItems();

        if ($quantities === []) {
            return collect();
        }

        return Product::query()
            ->whereIn('id', array_keys($quantities))
            ->with('category', 'images')
            ->get()
            ->map(fn (Product $product) => $this->line($product, $quantities[$product->id]))
            ->values();
    }

    /**
     * @return array{product: Product, quantity: int, subtotal: float}
     */
    private function line(Product $product, int $quantity): array
    {
        return [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => round($quantity * (float) $product->price, 2),
        ];
    }

    /**
     * @return array{error: bool, message: string}
     */
    public function add(Product $product, int $quantity): array
    {
        if (! $product->isApproved()) {
            return $this->fail('That product is not available.');
        }

        $quantity = max(1, min($quantity, $this->maxPerItem()));
        $already = $this->quantityOf($product->id);
        $wanted = min($already + $quantity, $this->maxPerItem());

        if ($already >= $this->maxPerItem()) {
            return $this->fail("You can only order {$this->maxPerItem()} of this item.");
        }

        if ($product->stock < $wanted) {
            return $this->fail(
                $product->stock > 0
                    ? "Only {$product->stock} left in stock."
                    : 'That product is out of stock.'
            );
        }

        $this->put($product->id, $wanted);

        return ['error' => false, 'message' => 'Item added to cart'];
    }

    /**
     * @return array{error: bool, message: string}
     */
    public function update(int $productId, int $quantity): array
    {
        $product = Product::find($productId);

        if (! $product || $this->quantityOf($productId) === 0) {
            return $this->fail('Product not found in cart.');
        }

        if ($quantity > $this->maxPerItem()) {
            return $this->fail("Maximum quantity per product is {$this->maxPerItem()}.");
        }

        $quantity = max(1, $quantity);

        if ($product->stock < $quantity) {
            return $this->fail("Only {$product->stock} left in stock.");
        }

        $this->put($productId, $quantity);

        return ['error' => false, 'message' => 'Updated'];
    }

    /**
     * @return array{error: bool, message: string}
     */
    public function remove(int $productId): array
    {
        if ($user = Auth::user()) {
            // Scoped to the signed-in user, so a crafted id cannot reach
            // somebody else's basket.
            CartLine::where('user_id', $user->id)->where('product_id', $productId)->delete();
        } else {
            $items = $this->sessionItems();
            unset($items[$productId]);
            $this->putSessionItems($items);
        }

        return ['error' => false, 'message' => 'Deleted'];
    }

    public function quantityOf(int $productId): int
    {
        if ($user = Auth::user()) {
            return (int) CartLine::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->value('quantity');
        }

        return (int) ($this->sessionItems()[$productId] ?? 0);
    }

    private function put(int $productId, int $quantity): void
    {
        if ($user = Auth::user()) {
            CartLine::updateOrCreate(
                ['user_id' => $user->id, 'product_id' => $productId],
                ['quantity' => $quantity]
            );

            return;
        }

        $items = $this->sessionItems();
        $items[$productId] = $quantity;
        $this->putSessionItems($items);
    }

    public function totalQuantity(): int
    {
        return (int) $this->lines()->sum('quantity');
    }

    public function count(): int
    {
        return $this->lines()->count();
    }

    public function total(): float
    {
        return round($this->lines()->sum('subtotal'), 2);
    }

    public function isEmpty(): bool
    {
        return $this->lines()->isEmpty();
    }

    public function clear(): void
    {
        if ($user = Auth::user()) {
            CartLine::where('user_id', $user->id)->delete();
        }

        Session::forget(self::SESSION_KEY);
    }

    /**
     * Fold a guest basket into the signed-in customer's own.
     *
     * Called the moment someone signs in, so a basket built before registering
     * is never silently abandoned. Quantities are combined rather than
     * overwritten, and still capped.
     */
    public function mergeGuestBasket(): void
    {
        $guestItems = $this->sessionItems();

        Session::forget(self::SESSION_KEY);

        if ($guestItems === [] || ! ($user = Auth::user())) {
            return;
        }

        foreach ($guestItems as $productId => $quantity) {
            $existing = (int) CartLine::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->value('quantity');

            $combined = min($existing + $quantity, $this->maxPerItem());

            CartLine::updateOrCreate(
                ['user_id' => $user->id, 'product_id' => (int) $productId],
                ['quantity' => $combined]
            );
        }
    }

    /**
     * @return array{error: bool, message: string}
     */
    private function fail(string $message): array
    {
        return ['error' => true, 'message' => $message];
    }
}
