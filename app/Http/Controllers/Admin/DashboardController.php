<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SalesReport;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(SalesReport $report): View
    {
        return view('admin.dashboard', [
            'totalEarnings' => $report->totalEarnings(),
            'earningsToday' => $report->earningsToday(),
            'orderCount' => $report->orderCount(),
            'customerCount' => $report->customerCount(),
            'dailyRevenue' => $report->dailyRevenue(),
            'salesByCategory' => $report->salesByCategory(),
        ]);
    }
}
