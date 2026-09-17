@extends('layouts.shop')

@section('title', 'Everything delivered in minutes')

@section('content')
    <section class="tf-slideshow slider-effect-fade slider-accessories mt-3">
        <div class="swiper tf-sw-slideshow" data-preview="1.6" data-tablet="1" data-mobile="1" data-centered="true"
            data-space="30" data-loop="true" data-auto-play="false" data-delay="2000" data-speed="1000">
            <div class="swiper-wrapper">
                @foreach ($slides as $slide)
                    <div class="swiper-slide" lazy="true">
                        <div class="wrap-slider">
                            <img src="{{ asset($slide['image']) }}" alt="slide-img">
                            <div class="box-content text-center">
                                <div class="container">
                                    <h2 class="fade-item fade-item-1 heading">{{ $slide['heading'] }}</h2>
                                    <div class="fade-item fade-item-3">
                                        <a href="#shophero" class="tf-btn btn-outline-dark fw-5 btn-xl radius-60">
                                            <span>Shop Now</span><i class="icon icon-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="container wrap-navigation">
            <div class="nav-sw style-white nav-next-slider navigation-next-slider box-icon w_46 round">
                <span class="icon icon-arrow-left"></span>
            </div>
            <div class="nav-sw style-white nav-prev-slider navigation-prev-slider box-icon w_46 round">
                <span class="icon icon-arrow-right"></span>
            </div>
        </div>
    </section>

    <section class="flat-spacing-11 pb-0">
        <div class="container">
            <div class="position-relative">
                <div class="flat-title flex-row justify-content-between px-0">
                    <span class="title wow fadeInUp" data-wow-delay="0s">Shop by category</span>
                </div>
                <div class="sw-pagination-wrapper">
                    <div class="swiper tf-sw-collection" data-preview="4" data-tablet="3" data-mobile="1"
                        data-space-lg="30" data-space-md="30" data-space="15" data-loop="false" data-auto-play="false">
                        <div class="swiper-wrapper">
                            @foreach ($categories as $category)
                                <div class="swiper-slide" lazy="true">
                                    <div class="collection-item-v2 type-small hover-img">
                                        <a href="{{ url('/category/'.$category->slug) }}" class="collection-inner">
                                            <div class="collection-image img-style radius-10">
                                                <img class="lazyload" data-src="{{ asset($category->image) }}"
                                                    src="{{ asset($category->image) }}" alt="{{ $category->name }}">
                                            </div>
                                            <div class="collection-content">
                                                <div class="top">
                                                    <h5 class="heading fw-5">{{ $category->name }}</h5>
                                                    <p class="subheading">{{ $category->products_count }} items</p>
                                                </div>
                                                <div class="bottom">
                                                    <button
                                                        class="tf-btn collection-title hover-icon btn-light rounded-full">
                                                        <span>Shop now</span><i class="icon icon-arrow1-top-left"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="box-sw-navigation">
                        <div class="sw-dots style-2 medium sw-pagination-collection justify-content-center"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="flat-spacing-8 mb-5 pb-0" id="shophero">
        <div class="container">
            <div class="grid-layout wrapper-shop" data-grid="grid-4">
                @foreach ($products as $product)
                    @include('shop.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </section>
@endsection
