@extends('layouts.shop')

@section('title', 'Page not found')

@section('content')
    <section class="page-404-wrap">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="image">
                        <img src="{{ asset('assets/images/item/404.svg') }}" alt="">
                    </div>
                    <div class="title">
                        Oops...That link is broken.
                    </div>
                    <p>Sorry for the inconvenience. Go to our homepage to check out our latest collections.</p>
                    <a href="{{ route('home') }}"
                        class="tf-btn btn-sm radius-3 btn-fill btn-icon animate-hover-btn">Shop now</a>
                </div>
            </div>
        </div>
    </section>
@endsection
