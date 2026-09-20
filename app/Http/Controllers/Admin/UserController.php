<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * One screen for every kind of account, filtered by role.
 *
 * Nothing here ever puts a password hash into a response: the edit form starts
 * empty, and leaving it empty keeps the existing password rather than replacing
 * it with a hash of nothing.
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->query('role');
        $search = trim((string) $request->query('search', ''));

        return view('admin.users.index', [
            'role' => $role,
            'search' => $search,
            'roles' => UserRole::cases(),
            'users' => User::query()
                ->when($role, fn ($query) => $query->where('role', $role))
                ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                        ->orWhere('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%");
                }))
                ->withCount('orders')
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => UserRole::cases(),
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('status', "{$user->full_name} has been added.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => UserRole::cases(),
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // An empty password field means "leave it alone", not "set it to
        // nothing" — the field is never prefilled, so blank is the normal case.
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $this->guardAgainstSelfLockout($request, $user, $data);

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', "{$user->full_name} has been updated.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'You cannot remove your own account.']);
        }

        // Soft deleted: orders reference their customer, and a hard delete
        // would take that history with it.
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', "{$user->full_name} has been removed.");
    }

    /**
     * Stop an admin editing away their own access and locking themselves out.
     *
     * @param  array<string, mixed>  $data
     */
    private function guardAgainstSelfLockout(Request $request, User $user, array &$data): void
    {
        if (! $request->user()->is($user)) {
            return;
        }

        $data['role'] = $user->role;
        $data['status'] = $user->status;
    }
}
