@extends('layouts.shop')

@section('title', 'My Account')

@section('content')
    @include('partials.storefront.page-title', ['title' => 'My Account'])

    <section class="flat-spacing-11">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    @include('partials.storefront.account-nav')
                </div>
                <div class="col-lg-9">
                    <div class="my-account-content account-dashboard">
                        <div class="mb_60">
                            <h5 class="fw-5 mb_20">Hello, {{ auth()->user()->firstname }}</h5>

                            @include('shop.partials.flash')

                            <form method="POST" action="{{ route('account.update') }}" class="form-checkout">
                                @csrf
                                @method('PATCH')
                                @include('shop.partials.billing-fields')

                                <button type="submit"
                                    class="tf-btn radius-3 btn-fill btn-icon animate-hover-btn justify-content-center">
                                    Update Details
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
