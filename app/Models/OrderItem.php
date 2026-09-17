<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single line of a placed order.
 *
 * `unit_price`, `seller_id` and `commission_rate` are copied here when the
 * order is created and are never written again. Every figure this class
 * reports is derived from those stored values rather than from the product,
 * so editing a product later cannot alter what an order was worth or what a
 * merchant earned from it.
 */
class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'seller_id',
        'quantity',
        'unit_price',
        'commission_rate',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'commission_rate' => 'decimal:2',
        ];
    }

    // Relationships

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // Money

    /**
     * What the customer paid for this line.
     */
    public function getSubtotalAttribute(): float
    {
        return round($this->quantity * (float) $this->unit_price, 2);
    }

    /**
     * The platform's cut of this line.
     */
    public function getCommissionAttribute(): float
    {
        return round($this->subtotal * (float) $this->commission_rate / 100, 2);
    }

    /**
     * What the merchant earned from this line.
     */
    public function getNetEarningsAttribute(): float
    {
        return round($this->subtotal - $this->commission, 2);
    }

    public function scopeForSeller(Builder $query, int $sellerId): Builder
    {
        return $query->where('seller_id', $sellerId);
    }
}
