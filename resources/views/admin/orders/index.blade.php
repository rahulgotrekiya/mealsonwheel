@extends('layouts.panel')

@section('title', 'Orders')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Orders</h5>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="search" name="search" class="form-control"
                        placeholder="Transaction ID, customer name or email" value="{{ $search }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $option)
                            <option value="{{ $option->value }}" @selected($status === $option->value)>
                                {{ $option->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    @if ($search !== '' || $status)
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-light">Clear</a>
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
                            <th>Transaction ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Placed</th>
                            <th>Status</th>
                            <th>Update Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td><code>{{ $order->transaction_id }}</code></td>
                                <td>
                                    {{ $order->user->full_name }}<br>
                                    <small class="text-muted">{{ $order->user->email }}</small>
                                </td>
                                <td class="text-success">&#8377; {{ number_format($order->total_amount, 2) }}</td>
                                <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <span class="badge {{ $order->status->badgeClass() }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td>
                                    {{-- A status change alters data, so it is a PATCH with a token, not a link. --}}
                                    <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="form-select form-select-sm d-inline w-auto"
                                            onchange="this.form.submit()">
                                            @foreach ($statuses as $option)
                                                <option value="{{ $option->value }}"
                                                    @selected($order->status === $option)>{{ $option->label() }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                        class="btn btn-info btn-sm">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $orders->links() }}
        </div>
    </div>
@endsection
