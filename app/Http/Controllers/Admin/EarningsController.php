<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SalesReport;
use Illuminate\View\View;

class EarningsController extends Controller
{
    public function __invoke(): View
    {
        $perSeller = SalesReport::perSeller();
        $store = new SalesReport;

        return view('admin.earnings', [
            'perSeller' => $perSeller,
            'storeGross' => $store->totalEarnings(),
            'storeCommission' => $store->commission(),
            // What suppliers have earned in total. Nothing is ever marked paid,
            // so everything earned is still outstanding.
            'owedToMerchants' => $perSeller->sum('net'),
        ]);
    }
}
