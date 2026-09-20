@extends('layouts.panel')

@section('title', 'Order #'.$order->id)

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex">
                    <h5 class="card-title flex-grow-1 mb-0">Order #{{ $order->id }}</h5>
                    <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
                </div>
                <div class="card-body">
                    <table class="table mb-0">
                        <tr>
                            <th>Transaction ID</th>
                            <td><code>{{ $order->transaction_id }}</code></td>
                        </tr>
                        <tr>
                            <th>Total Amount</th>
                            <td>&#8377; {{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Placed</th>
                            <td>{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </table>

                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-3">
                        @csrf
                        @method('PATCH')
                        <label class="form-label" for="order-status">Update status</label>
                        <div class="d-flex gap-2">
                            <select name="status" id="order-status" class="form-select">
                                @foreach ($statuses as $option)
                                    <option value="{{ $option->value }}" @selected($order->status === $option)>
                                        {{ $option->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Customer</h5>
                </div>
                <div class="card-body">
                    <table class="table mb-0">
                        <tr>
                            <th>Name</th>
                            <td>{{ $order->user->full_name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $order->user->email }}</td>
                        </tr>
                        <tr>
                            <th>Contact</th>
                            <td>{{ $order->user->phone ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Delivery address</th>
                            <td>{{ $order->user->address?->single_line ?? 'No address on file' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ordered Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Supplier</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>
                                            <a href="{{ route('product', $item->product) }}">
                                                {{ $item->product->name }}
                                            </a>
                                        </td>
                                        <td>{{ $item->seller?->full_name ?? 'Store' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        {{-- The price this line was sold at, not the price today. --}}
                                        <td>&#8377; {{ number_format($item->unit_price, 2) }}</td>
                                        <td>&#8377; {{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Order Total</th>
                                    <th>&#8377; {{ number_format($order->total_amount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
