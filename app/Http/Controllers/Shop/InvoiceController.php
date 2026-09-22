<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function __invoke(Order $order): Response
    {
        // The same policy that guards the order screen. An invoice carries a
        // name, address and purchase history, so it is not a public document.
        $this->authorize('view', $order);

        return Pdf::loadView('pdf.invoice', [
            'order' => $order->load('items.product', 'user.address'),
        ])->download("invoice-{$order->id}.pdf");
    }
}
