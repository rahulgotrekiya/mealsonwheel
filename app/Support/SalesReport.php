<?php

namespace App\Support;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Sales figures for the staff dashboards.
 *
 * Every amount is derived from `order_items.unit_price` — the price recorded
 * when the sale happened — never from the product's current price. That keeps
 * the admin dashboard, the merchant dashboard and the earnings report all
 * quoting the same numbers, and stops a price edit rewriting history.
 *
 * Passing a seller id narrows every figure to that merchant's own lines, which
 * is what the merchant panel does.
 */
class SalesReport
{
    public function __construct(private readonly ?int $sellerId = null) {}

    public static function forSeller(int $sellerId): self
    {
        return new self($sellerId);
    }

    /**
     * Order lines that count as revenue: a cancelled or returned order is not a sale.
     */
    private function soldItems(): Builder
    {
        return OrderItem::query()
            ->when($this->sellerId, fn (Builder $q) => $q->where('seller_id', $this->sellerId))
            ->whereHas('order', fn (Builder $q) => $q->whereNotIn('status', [
                OrderStatus::Cancelled,
                OrderStatus::Returned,
            ]));
    }

    public function totalEarnings(): float
    {
        return (float) $this->soldItems()->sum(DB::raw('quantity * unit_price'));
    }

    public function earningsToday(): float
    {
        return (float) $this->soldItems()
            ->whereDate('created_at', today())
            ->sum(DB::raw('quantity * unit_price'));
    }

    public function unitsSold(): int
    {
        return (int) $this->soldItems()->sum('quantity');
    }

    /**
     * Commission the platform kept, at the rate each sale was made under.
     */
    public function commission(): float
    {
        return (float) $this->soldItems()->sum(DB::raw('quantity * unit_price * commission_rate / 100'));
    }

    /**
     * What the merchant keeps once commission is taken off.
     */
    public function netEarnings(): float
    {
        return round($this->totalEarnings() - $this->commission(), 2);
    }

    public function orderCount(): int
    {
        if ($this->sellerId) {
            // An order counts once for a merchant however many of its lines
            // they supplied.
            return Order::containingSeller($this->sellerId)->count();
        }

        return Order::count();
    }

    public function customerCount(): int
    {
        return User::role(UserRole::Customer)->count();
    }

    /**
     * Revenue per day for the last N days, with quiet days included as zero so
     * the chart keeps an even axis.
     *
     * @return Collection<string, float>
     */
    public function dailyRevenue(int $days = 10): Collection
    {
        $takings = $this->soldItems()
            ->selectRaw('DATE(order_items.created_at) as day, SUM(quantity * unit_price) as revenue')
            ->where('order_items.created_at', '>=', today()->subDays($days - 1))
            ->groupBy('day')
            ->pluck('revenue', 'day');

        return collect(range($days - 1, 0))
            ->mapWithKeys(function (int $ago) use ($takings) {
                $day = Carbon::today()->subDays($ago)->toDateString();

                return [$day => round((float) ($takings[$day] ?? 0), 2)];
            });
    }

    /**
     * Units sold per category.
     *
     * @return Collection<string, int>
     */
    public function salesByCategory(): Collection
    {
        return $this->soldItems()
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw('categories.name as category, SUM(order_items.quantity) as units')
            ->groupBy('categories.name')
            ->orderByDesc('units')
            ->pluck('units', 'category')
            ->map(fn ($units) => (int) $units);
    }

    /**
     * Earnings broken down by supplier, for the admin view.
     *
     * One grouped query rather than a report per merchant, and the commission
     * is summed at the rate each line was actually sold under.
     *
     * @return Collection<int, object>
     */
    public static function perSeller(): Collection
    {
        /*
         * Deliberately the query builder rather than Eloquent.
         *
         * Hydrating an aggregate into OrderItem would let the model's own
         * `commission` and `subtotal` accessors shadow the summed columns —
         * they recompute per row from `commission_rate`, which a grouped query
         * does not select, so every total would silently read as zero. Plain
         * rows have no accessors to get in the way.
         */
        return DB::table('order_items')
            ->join('users', 'users.id', '=', 'order_items.seller_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNotIn('orders.status', [
                OrderStatus::Cancelled->value,
                OrderStatus::Returned->value,
            ])
            ->groupBy('order_items.seller_id', 'users.firstname', 'users.lastname', 'users.email')
            ->selectRaw('
                order_items.seller_id,
                users.firstname,
                users.lastname,
                users.email,
                SUM(order_items.quantity) as units,
                SUM(order_items.quantity * order_items.unit_price) as gross,
                SUM(order_items.quantity * order_items.unit_price * order_items.commission_rate / 100) as commission
            ')
            ->orderByDesc('gross')
            ->get()
            ->map(function (object $row): object {
                $row->units = (int) $row->units;
                $row->gross = round((float) $row->gross, 2);
                $row->commission = round((float) $row->commission, 2);
                $row->net = round($row->gross - $row->commission, 2);

                return $row;
            });
    }
}
