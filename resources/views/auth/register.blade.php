@extends('layouts.shop')

@section('title', 'Sign up')

@section('content')
    @include('partials.storefront.page-title', ['title' => 'Sign up'])

    <section class="flat-spacing-11">
        <div class="container">
            <div class="tf-login-form">
                @if ($errors->any())
                    <div class="callout callout-danger text-center">
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                <form action="{{ route('register.store') }}" method="POST">
                    @csrf
                    <div class="tf-field style-1 mb-2">
                        <input class="tf-field-input tf-input" placeholder=" " type="text" name="firstname"
                            value="{{ old('firstname') }}" required>
                        <label class="tf-field-label">First name</label>
                    </div>
                    <div class="tf-field style-1 mb-2">
                        <input class="tf-field-input tf-input" placeholder=" " type="text" name="lastname"
                            value="{{ old('lastname') }}" required>
                        <label class="tf-field-label">Last name</label>
                    </div>
                    <div class="tf-field style-1 mb-2">
                        <input class="tf-field-input tf-input" placeholder=" " type="email" name="email"
                            value="{{ old('email') }}" required autocomplete="email">
                        <label class="tf-field-label">Email *</label>
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
                                <span>Signup</span>
                            </button>
                        </div>
                        <div class="w-100">
                            <a href="{{ route('login') }}" class="btn-link fw-6 w-100 link">
                                Already have an account? Log in here
                                <i class="icon icon-arrow1-top-left"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
