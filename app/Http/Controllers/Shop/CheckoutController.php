<?php

namespace App\Http\Controllers\Shop;

use App\Actions\PlaceOrder;
use App\Exceptions\CheckoutException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\BillingRequest;
use App\Http\Requests\Shop\PaymentRequest;
use App\Models\Address;
use App\Models\Order;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly Cart $cart) {}

    public function create(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart');
        }

        return view('shop.checkout', [
            'lines' => $this->cart->lines(),
            'total' => $this->cart->total(),
            'address' => auth()->user()->address,
        ]);
    }

    /**
     * Save the billing details, which are the delivery details too.
     */
    public function storeBilling(BillingRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update($request->safe()->only('firstname', 'lastname', 'phone'));

        Address::updateOrCreate(
            ['user_id' => $user->id],
            $request->safe()->only('street', 'city', 'state', 'zip_code')
        );

        return back()->with('status', 'Your details have been saved.');
    }

    public function payment(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart');
        }

        // Somewhere to send the order is not optional.
        if (! auth()->user()->address) {
            return redirect()->route('checkout')
                ->withErrors(['street' => 'Please add a delivery address before paying.']);
        }

        return view('shop.payment', ['total' => $this->cart->total()]);
    }

    /**
     * Take the (simulated) payment and place the order.
     */
    public function pay(PaymentRequest $request, PlaceOrder $placeOrder): RedirectResponse
    {
        try {
            $order = $placeOrder($request->user());
        } catch (CheckoutException $e) {
            // Stock ran out, or the basket emptied, between here and the
            // payment form. Send them back with something they can act on.
            return redirect()->route('cart')->withErrors(['payment' => $e->getMessage()]);
        }

        return redirect()->route('checkout.confirmation', $order);
    }

    public function confirmation(Order $order): View
    {
        $this->authorize('view', $order);

        return view('shop.confirmation', ['order' => $order]);
    }
}
