<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Support\SalesReport;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $merchant = auth()->user();

        // Every figure narrowed to this merchant's own order lines.
        $report = SalesReport::forSeller($merchant->id);

        return view('merchant.dashboard', [
            'unitsSold' => $report->unitsSold(),
            'grossSales' => $report->totalEarnings(),
            'commission' => $report->commission(),
            'netEarnings' => $report->netEarnings(),
            'orderCount' => $report->orderCount(),
            'productCount' => $merchant->products()->count(),
            'lowStock' => $merchant->products()->lowStock()->orderBy('stock')->get(),
            'dailyRevenue' => $report->dailyRevenue(),
        ]);
    }
}
