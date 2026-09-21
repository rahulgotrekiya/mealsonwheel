<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MerchantRegisterController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if ($user = Auth::user()) {
            return redirect($user->role->home());
        }

        return view('auth.merchant-register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        /*
         * A merchant account is created but not signed in: it sits pending
         * until an admin approves it. Anyone may apply, so nothing here grants
         * access on its own.
         */
        User::create([
            ...$request->safe()->except('password_confirmation'),
            'role' => UserRole::Merchant,
            'status' => UserStatus::Pending,
        ]);

        return redirect()
            ->route('login')
            ->with('status', 'Thanks for applying. We will review your application and email you once it has been approved.');
    }
}
