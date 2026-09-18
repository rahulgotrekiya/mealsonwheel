<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\BillingRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function edit(): View
    {
        return view('shop.account', [
            'address' => auth()->user()->address,
        ]);
    }

    public function update(BillingRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update($request->safe()->only('firstname', 'lastname', 'phone'));

        Address::updateOrCreate(
            ['user_id' => $user->id],
            $request->safe()->only('street', 'city', 'state', 'zip_code')
        );

        return back()->with('status', 'Your details have been updated.');
    }
}
