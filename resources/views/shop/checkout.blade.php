@extends('layouts.shop')

@section('title', 'Checkout')

@section('content')
    @include('partials.storefront.page-title', ['title' => 'Checkout'])

    <section class="flat-spacing-11">
        <div class="container">
            @include('shop.partials.flash')

            <div class="tf-page-cart-wrap layout-2">
                <div class="tf-page-cart-item">
                    <h5 class="fw-5 mb_20">Billing details</h5>

                    <form method="POST" action="{{ route('checkout.billing') }}" class="form-checkout">
                        @csrf
                        @include('shop.partials.billing-fields')

                        <button type="submit"
                            class="tf-btn radius-3 btn-fill btn-icon animate-hover-btn justify-content-center">
                            Update Address
                        </button>
                    </form>
                </div>

                <div class="tf-page-cart-footer">
                    <div class="tf-cart-footer-inner">
                        <h5 class="fw-5 mb_20">Your order</h5>

                        <form class="tf-page-cart-checkout widget-wrap-checkout" method="GET"
                            action="{{ route('checkout.payment') }}">
                            <ul class="wrap-checkout-product">
                                @foreach ($lines as $line)
                                    <li class="checkout-product-item">
                                        <figure class="img-product">
                                            <img src="{{ asset($line['product']->primary_image) }}"
                                                alt="{{ $line['product']->name }}">
                                            <span class="quantity">{{ $line['quantity'] }}</span>
                                        </figure>
                                        <div class="content">
                                            <div class="info">
                                                <p class="name">{{ $line['product']->name }}</p>
                                                <span class="variant">{{ $line['product']->category->name }}</span>
                                            </div>
                                            <span class="price">&#8377;
                                                {{ number_format($line['product']->price, 2) }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="d-flex justify-content-between line pb_20">
                                <h6 class="fw-5">Total</h6>
                                <h6 class="total fw-5">&#8377; {{ number_format($total, 2) }}</h6>
                            </div>

                            <div class="wd-check-payment">
                                <div class="fieldset-radio mb_20">
                                    <input type="radio" name="payment" id="bank" class="tf-check" checked>
                                    <label for="bank">Debit Card / Credit Card</label>
                                </div>
                                <p class="text_black-2 mb_20">
                                    Your personal data will be used to process your order, support your experience
                                    throughout this website, and for other purposes described in our
                                    <a href="{{ route('privacy') }}" class="text-decoration-underline">privacy
                                        policy</a>.
                                </p>
                                <div class="box-checkfieldset-radio mb_20 fieldset-radio">
                                    <input type="checkbox" id="check-agree" class="tf-check" required>
                                    <label for="check-agree" class="text_black-2">I have read and agree to the website
                                        <a href="{{ route('terms') }}" target="_blank"
                                            class="text-decoration-underline">terms and conditions</a>.
                                    </label>
                                </div>
                            </div>

                            <button type="submit"
                                class="tf-btn radius-3 btn-fill btn-icon animate-hover-btn justify-content-center"
                                name="place_order">Place order</button>

                            @include('partials.storefront.payment-marks')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
