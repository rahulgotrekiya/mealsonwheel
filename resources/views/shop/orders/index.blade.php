@extends('layouts.shop')

@section('title', 'My Orders')

@section('content')
    @include('partials.storefront.page-title', ['title' => 'My Orders'])

    <section class="flat-spacing-11">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    @include('partials.storefront.account-nav')
                </div>
                <div class="col-lg-9">
                    @include('shop.partials.flash')

                    <div class="my-account-content account-order">
                        <div class="wrap-account-order">
                            <table>
                                <thead>
                                    <tr>
                                        <th class="fw-6">Order</th>
                                        <th class="fw-6">Date</th>
                                        <th class="fw-6">Status</th>
                                        <th class="fw-6">Total</th>
                                        <th class="fw-6">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($orders as $order)
                                        <tr class="tf-order-item">
                                            <td>#{{ $order->id }}</td>
                                            <td>{{ $order->created_at->format('F j, Y') }}</td>
                                            <td>
                                                <span class="badge {{ $order->status->badgeClass() }}">
                                                    {{ $order->status->label() }}
                                                </span>
                                            </td>
                                            <td>&#8377; {{ number_format($order->total_amount, 2) }}</td>
                                            <td>
                                                <a href="{{ route('orders.show', $order) }}"
                                                    class="tf-btn btn-fill animate-hover-btn rounded-0 justify-content-center">
                                                    <span>View</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No orders found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
