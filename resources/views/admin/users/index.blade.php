@extends('layouts.panel')

@section('title', 'Users')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1">Users</h5>
            <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                <i class="ri-add-line align-bottom me-1"></i> Add User
            </a>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="search" name="search" class="form-control" placeholder="Name or email"
                        value="{{ $search }}">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">All roles</option>
                        @foreach ($roles as $option)
                            <option value="{{ $option->value }}" @selected($role === $option->value)>
                                {{ $option->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    @if ($search !== '' || $role)
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered nowrap table-striped align-middle">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Orders</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->full_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? '—' }}</td>
                                <td>{{ $user->role->label() }}</td>
                                <td>
                                    <span class="badge {{ $user->status->badgeClass() }}">
                                        {{ $user->status->label() }}
                                    </span>
                                </td>
                                <td>{{ $user->orders_count }}</td>
                                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                        class="btn btn-primary btn-sm">Edit</a>
                                    @unless (auth()->user()->is($user))
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                            class="d-inline"
                                            onsubmit="return confirm('Remove this account? Their order history is kept.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>
@endsection
