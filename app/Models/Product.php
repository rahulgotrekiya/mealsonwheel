<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Support\RichText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'seller_id',
        'name',
        'slug',
        'description',
        'additional_info',
        'price',
        'stock',
        'status',
        'reject_reason',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'views' => 'integer',
            'status' => ProductStatus::class,
        ];
    }

    // Relationships

    /*
     * Rich text is cleaned on the way in rather than on the way out.
     *
     * A description is written in an editor and rendered as HTML rather than
     * escaped, which makes it the one place where somebody else's markup
     * reaches a customer's browser — and merchants sign themselves up, so that
     * markup is not trustworthy.
     *
     * Cleaning here rather than in a controller covers every write: a form, a
     * seeder, a console command, anything added later. There is no path that
     * can put unsafe markup into the column.
     */
    protected function description(): Attribute
    {
        return Attribute::set(fn (?string $value) => RichText::clean($value));
    }

    protected function additionalInfo(): Attribute
    {
        return Attribute::set(fn (?string $value) => RichText::clean($value));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes

    /**
     * Only products cleared for the storefront. Every public catalog query
     * goes through this, so an unreviewed listing can never leak into the shop.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::Approved);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeForSeller(Builder $query, int $sellerId): Builder
    {
        return $query->where('seller_id', $sellerId);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('stock', '<=', config('marketplace.low_stock_threshold'));
    }

    // Accessors

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getPrimaryImageAttribute(): string
    {
        return $this->images->first()?->path ?? 'images/placeholder.jpg';
    }

    public function isApproved(): bool
    {
        return $this->status === ProductStatus::Approved;
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }
}
