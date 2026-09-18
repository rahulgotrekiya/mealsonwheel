@extends('layouts.shop')

@section('title', 'Order #'.$order->id)

@section('content')
    @include('partials.storefront.page-title', ['title' => 'Order #'.$order->id])

    <section class="flat-spacing-11">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    @include('partials.storefront.account-nav')
                </div>
                <div class="col-lg-9">
                    @include('shop.partials.flash')

                    <div class="wd-form-order">
                        <div class="order-head">
                            <figure class="img-product">
                                <img src="{{ asset($order->items->first()?->product?->primary_image ?? 'images/placeholder.jpg') }}"
                                    alt="Order #{{ $order->id }}">
                            </figure>
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <div class="content">
                                    <div class="badge {{ $order->status->badgeClass() }}">
                                        {{ $order->status->label() }}
                                    </div>
                                    <h6 class="mt-8 fw-5">Order #{{ $order->id }}</h6>
                                    <p class="mt-8 fw-5">Payment ID: #{{ $order->transaction_id }}</p>
                                    <p class="mt-8 fw-5">Order Date: {{ $order->created_at->format('F j, Y') }}</p>
                                    <p class="mt-8 fw-5">
                                        Status:
                                        <span
                                            class="{{ $order->status === \App\Enums\OrderStatus::Cancelled ? 'text-danger' : 'text-success' }}">
                                            {{ $order->status->estimatedDelivery($order->created_at) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="ms-auto">
                                    @if ($order->isCancellable())
                                        <form method="POST" action="{{ route('orders.cancel', $order) }}"
                                            onsubmit="return confirm('Are you sure you want to cancel this order? This action cannot be undone.');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn fw-6 btn-danger">
                                                Cancel Order
                                            </button>
                                        </form>
                                    @elseif ($order->status === \App\Enums\OrderStatus::Shipped)
                                        <span class="text-muted">Order cannot be cancelled at this stage</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="widget-content-inner">
                            <h6 class="mt-8 fw-5 mb-4">Order Items</h6>

                            @foreach ($order->items as $item)
                                <div class="order-head">
                                    <figure class="img-product">
                                        <img src="{{ asset($item->product->primary_image) }}"
                                            alt="{{ $item->product->name }}">
                                    </figure>
                                    <div class="content">
                                        <div class="text-2 fw-6">
                                            <a href="{{ route('product', $item->product) }}">{{ $item->product->name }}</a>
                                        </div>
                                        {{-- Price as it was when the order was placed, not as it is today. --}}
                                        <div class="mt_4"><span class="fw-6">Price:</span> &#8377;
                                            {{ number_format($item->unit_price, 2) }}</div>
                                        <div class="mt_4"><span class="fw-6">Quantity:</span> {{ $item->quantity }}
                                        </div>
                                        <div class="mt_4"><span class="fw-6">Total Price:</span> &#8377;
                                            {{ number_format($item->subtotal, 2) }}</div>
                                    </div>
                                </div>
                            @endforeach

                            <ul>
                                <li class="d-flex justify-content-between text-2 mt_8">
                                    <span>Order Total</span>
                                    <span class="fw-6">&#8377; {{ number_format($order->total_amount, 2) }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
