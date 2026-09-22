<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Support\SalesReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

/**
 * Printable versions of the admin screens.
 *
 * Rendered from Blade templates rather than drawn cell by cell, so the layouts
 * stay editable and share the same figures as the screens they mirror.
 */
class ReportController extends Controller
{
    public function orders(): Response
    {
        return Pdf::loadView('pdf.orders', [
            'orders' => Order::with('user')->latest('id')->get(),
        ])
            ->setPaper('a4', 'landscape')
            ->download('orders-report-'.now()->format('Y-m-d').'.pdf');
    }

    public function users(): Response
    {
        $groups = User::orderBy('id')
            ->get()
            ->groupBy(fn (User $user) => $user->role->label())
            // Roles in a stable order, so two exports of the same data match.
            ->sortKeys();

        return Pdf::loadView('pdf.users', ['groups' => $groups])
            ->download('accounts-report-'.now()->format('Y-m-d').'.pdf');
    }

    public function earnings(): Response
    {
        return Pdf::loadView('pdf.earnings', [
            'perSeller' => SalesReport::perSeller(),
        ])
            ->setPaper('a4', 'landscape')
            ->download('merchant-earnings-'.now()->format('Y-m-d').'.pdf');
    }
}
