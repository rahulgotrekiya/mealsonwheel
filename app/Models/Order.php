<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_id',
        'total_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'status' => OrderStatus::class,
        ];
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes

    /**
     * Orders containing at least one line supplied by the given merchant.
     *
     * Note this narrows which orders are visible, not which lines: a merchant
     * view must still filter the items themselves, since one order can span
     * several merchants.
     */
    public function scopeContainingSeller(Builder $query, int $sellerId): Builder
    {
        return $query->whereHas('items', fn (Builder $items) => $items->where('seller_id', $sellerId));
    }

    // Helpers

    public function isCancellable(): bool
    {
        return $this->status->isCancellable();
    }

    /**
     * The portion of this order supplied by one merchant.
     *
     * Merchants are shown this instead of `total_amount`, which covers the
     * whole basket and may include lines from other merchants entirely.
     */
    public function subtotalForSeller(int $sellerId): float
    {
        return round(
            $this->items->where('seller_id', $sellerId)->sum(fn (OrderItem $item) => $item->subtotal),
            2
        );
    }
}
