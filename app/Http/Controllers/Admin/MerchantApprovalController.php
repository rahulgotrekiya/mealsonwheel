<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Mail\MerchantApplicationReviewed;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * The queue of suppliers waiting to be let in.
 *
 * Approving is the only thing that turns a merchant application into an
 * account that can sign in, so this is the gate the whole merchant panel sits
 * behind.
 */
class MerchantApprovalController extends Controller
{
    public function index(): View
    {
        return view('admin.merchants.index', [
            'pending' => User::awaitingApproval()->latest('id')->paginate(20),
            'reviewed' => User::role(UserRole::Merchant)
                ->whereIn('status', [UserStatus::Active, UserStatus::Suspended])
                ->latest('id')
                ->paginate(20, ['*'], 'reviewed'),
        ]);
    }

    public function approve(User $user): RedirectResponse
    {
        if ($error = $this->rejectNonApplicant($user)) {
            return $error;
        }

        $user->update(['status' => UserStatus::Active]);

        Mail::to($user->email)->send(new MerchantApplicationReviewed($user, approved: true));

        return back()->with('status', "{$user->full_name} can now sign in and list products.");
    }

    public function reject(User $user): RedirectResponse
    {
        if ($error = $this->rejectNonApplicant($user)) {
            return $error;
        }

        // Suspended rather than deleted, so the decision is recorded and can be
        // reversed without the applicant having to sign up again.
        $user->update(['status' => UserStatus::Suspended]);

        Mail::to($user->email)->send(new MerchantApplicationReviewed($user, approved: false));

        return back()->with('status', "{$user->full_name}'s application was declined.");
    }

    private function rejectNonApplicant(User $user): ?RedirectResponse
    {
        if (! $user->isMerchant()) {
            return back()->withErrors(['user' => 'That account is not a merchant application.']);
        }

        return null;
    }
}
