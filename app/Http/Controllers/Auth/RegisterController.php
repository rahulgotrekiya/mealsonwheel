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

class RegisterController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if ($user = Auth::user()) {
            return redirect($user->role->home());
        }

        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        // Public sign-up creates customers only. Admin accounts are made from
        // the admin panel, and merchants register through their own route.
        $user = User::create([
            ...$request->safe()->except('password_confirmation'),
            'role' => UserRole::Customer,
            'status' => UserStatus::Active,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($user->role->home());
    }
}
