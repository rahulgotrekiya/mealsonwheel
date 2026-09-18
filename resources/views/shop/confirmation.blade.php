@extends('layouts.shop')

@section('title', 'Order Confirmation')

@section('content')
    @include('partials.storefront.page-title', ['title' => 'Order Confirmation'])

    <section class="flat-spacing-11">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="tf-page-cart-checkout">
                        <h5 class="fw-5 mb_20">Order Confirmation</h5>
                        <p>Thank you for your payment!</p>
                        <p><strong>Transaction ID:</strong> {{ $order->transaction_id }}</p>
                        <p><strong>Order:</strong> #{{ $order->id }}</p>
                        <p><strong>Total:</strong> &#8377; {{ number_format($order->total_amount, 2) }}</p>
                        <p>Your order has been successfully placed. We will process your order shortly, and a
                            confirmation has been sent to {{ $order->user->email }}.</p>

                        <a href="{{ route('orders.show', $order) }}"
                            class="tf-btn radius-3 btn-fill btn-icon animate-hover-btn justify-content-center mt-3">
                            View order
                        </a>
                        <a href="{{ route('shop') }}"
                            class="tf-btn btn-line radius-3 justify-content-center mt-3">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
