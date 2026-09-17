@extends('layouts.shop')

@section('title', 'Log in')

@section('content')
    @include('partials.storefront.page-title', ['title' => 'Log in'])

    <section class="flat-spacing-11">
        <div class="container">
            <div class="tf-login-form">
                @if ($errors->any())
                    <div class="callout callout-danger text-center">
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                @if (session('status'))
                    <div class="callout callout-success text-center">
                        <p>{{ session('status') }}</p>
                    </div>
                @endif

                <form action="{{ route('login.store') }}" method="POST">
                    @csrf
                    <div class="tf-field style-1 mb-2">
                        <input class="tf-field-input tf-input" placeholder=" " type="email" name="email"
                            value="{{ old('email') }}" required autocomplete="email">
                        <label class="tf-field-label">Email *</label>
                    </div>
                    <div class="tf-field style-1 mb-2">
                        <input class="tf-field-input tf-input" placeholder=" " type="password" name="password" required
                            autocomplete="current-password">
                        <label class="tf-field-label">Password *</label>
                    </div>

                    <div class="bottom">
                        <div class="w-100 mb-2">
                            <button type="submit"
                                class="tf-btn btn-fill animate-hover-btn radius-3 w-100 justify-content-center">
                                <span>Log in</span>
                            </button>
                        </div>
                        <div class="w-100">
                            <a href="{{ route('register') }}" class="btn-link fw-6 w-100 link">
                                New customer? Create your account
                                <i class="icon icon-arrow1-top-left"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
