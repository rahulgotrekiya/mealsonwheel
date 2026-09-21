@extends('layouts.shop')

@section('title', 'Become a supplier')

@section('content')
    @include('partials.storefront.page-title', ['title' => 'Become a supplier'])

    <section class="flat-spacing-11">
        <div class="container">
            <div class="tf-login-form">
                @if ($errors->any())
                    <div class="callout callout-danger text-center">
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                <p class="text_black-2 mb_20">
                    Supply pet food through Meals on Wheels. You set your own prices and keep your stock
                    levels current; we hold the stock, handle delivery and take a
                    {{ (int) config('marketplace.commission_rate') }}% commission on each sale.
                </p>
                <p class="text_black-2 mb_20">
                    Applications are reviewed by hand. We will email you once yours has been looked at.
                </p>

                <form action="{{ route('merchant.register.store') }}" method="POST">
                    @csrf
                    <div class="tf-field style-1 mb-2">
                        <input class="tf-field-input tf-input" placeholder=" " type="text" name="firstname"
                            value="{{ old('firstname') }}" required>
                        <label class="tf-field-label">Business name</label>
                    </div>
                    <div class="tf-field style-1 mb-2">
                        <input class="tf-field-input tf-input" placeholder=" " type="text" name="lastname"
                            value="{{ old('lastname') }}" required>
                        <label class="tf-field-label">Trading name</label>
                    </div>
                    <div class="tf-field style-1 mb-2">
                        <input class="tf-field-input tf-input" placeholder=" " type="email" name="email"
                            value="{{ old('email') }}" required autocomplete="email">
                        <label class="tf-field-label">Email *</label>
                    </div>
                    <div class="tf-field style-1 mb-2">
                        <input class="tf-field-input tf-input" placeholder=" " type="text" name="phone"
                            value="{{ old('phone') }}">
                        <label class="tf-field-label">Phone</label>
                    </div>
                    <div class="tf-field style-1 mb-2">
                        <input class="tf-field-input tf-input" placeholder=" " type="password" name="password" required
                            autocomplete="new-password">
                        <label class="tf-field-label">Password *</label>
                    </div>

                    <div class="bottom">
                        <div class="w-100">
                            <button type="submit"
                                class="tf-btn btn-fill animate-hover-btn radius-3 w-100 justify-content-center">
                                <span>Apply to supply</span>
                            </button>
                        </div>
                        <div class="w-100">
                            <a href="{{ route('login') }}" class="btn-link fw-6 w-100 link">
                                Already approved? Log in here
                                <i class="icon icon-arrow1-top-left"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
