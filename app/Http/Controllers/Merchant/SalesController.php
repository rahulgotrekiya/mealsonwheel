<?php

namespace App\Http\Controllers\Merchant;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Support\SalesReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * What this supplier has sold.
 *
 * Read only, and scoped to their own order lines. A merchant supplies stock to
 * the warehouse and is paid for what sells; they do not fulfil anything, so
 * they are shown no customer name, address or contact detail, and the basket
 * total — which may include other suppliers' products — is never exposed.
 */
class SalesController extends Controller
{
    public function index(Request $request): View
    {
        $merchant = $request->user();
        $report = SalesReport::forSeller($merchant->id);

        return view('merchant.sales', [
            'sales' => OrderItem::query()
                ->forSeller($merchant->id)
                ->with('product', 'order')
                ->latest('id')
                ->paginate(25),
            'unitsSold' => $report->unitsSold(),
            'grossSales' => $report->totalEarnings(),
            'commission' => $report->commission(),
            'netEarnings' => $report->netEarnings(),
            'orderCount' => $report->orderCount(),
            // Cancelled and returned orders are excluded from the figures above,
            // so the list says which lines those are.
            'voidStatuses' => [OrderStatus::Cancelled, OrderStatus::Returned],
        ]);
    }
}
