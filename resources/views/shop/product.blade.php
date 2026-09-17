@extends('layouts.shop')

@section('title', $product->name)

@section('content')
    <section class="pt_0 mt-5 mb-2 single-product">
        <div class="tf-main-product">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="tf-product-media-wrap sticky-top">
                            <div class="thumbs-slider thumbs-default">
                                <div class="swiper tf-product-media-thumbs tf-product-media-thumbs-default"
                                    data-direction="vertical">
                                    <div class="swiper-wrapper stagger-wrap">
                                        @foreach ($product->images as $image)
                                            <div class="swiper-slide stagger-item">
                                                <div class="item">
                                                    <img class="lazyload" data-src="{{ asset($image->path) }}"
                                                        src="{{ asset($image->path) }}" alt="{{ $product->name }}">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="swiper tf-product-media-main tf-product-media-main-default">
                                    <div class="swiper-wrapper">
                                        @foreach ($product->images as $image)
                                            <div class="swiper-slide">
                                                <a href="#" class="item">
                                                    <img class="lazyload" data-src="{{ asset($image->path) }}"
                                                        src="{{ asset($image->path) }}" alt="{{ $product->name }}">
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="swiper-button-next button-style-arrow thumbs-next"></div>
                                    <div class="swiper-button-prev button-style-arrow thumbs-prev"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="tf-product-info-wrap position-relative">
                            <div class="tf-zoom-main"></div>

                            <div class="tf-product-info-list">
                                <div class="tf-breadcrumb">
                                    <div>
                                        <div
                                            class="tf-breadcrumb-wrap d-flex justify-content-between flex-wrap align-items-center py-0 mb-3">
                                            <div class="tf-breadcrumb-list">
                                                <a href="{{ route('home') }}" class="text">Home</a>
                                                <i class="icon icon-arrow-right"></i>
                                                <a href="{{ route('category', $product->category) }}"
                                                    class="text">{{ $product->category->name }}</a>
                                                <i class="icon icon-arrow-right"></i>
                                                <span class="text">{{ $product->name }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tf-product-info-title">
                                        <h5>{{ $product->name }}</h5>
                                    </div>

                                    <div class="tf-product-info-price mb-3">
                                        <div class="price-on-sale">&#8377; {{ number_format($product->price, 2) }}</div>
                                    </div>

                                    <div class="tf-product-info-variant-picker">
                                        <div class="variant-picker-item">
                                            <div class="variant-picker-label">
                                                Category: <span class="fw-6 variant-picker-label-value">
                                                    <a
                                                        href="{{ route('category', $product->category) }}">{{ $product->category->name }}</a>
                                                </span>
                                                <p class="fw-6 mt-1">Description</p>
                                                <p>{{ Str::limit(strip_tags($product->description), 180) }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tf-product-info-badges">
                                        <div class="product-status-content">
                                            <i class="icon-lightning"></i>
                                            @if ($product->isInStock())
                                                <p class="fw-6">Selling fast</p>
                                            @else
                                                <p class="fw-6">Out of stock</p>
                                            @endif
                                        </div>
                                    </div>

                                    @unless (auth()->check() && ! auth()->user()->isCustomer())
                                        <form class="form-inline" id="productForm">
                                            <div class="form-group tf-product-info-quantity">
                                                <div class="quantity-title fw-6">Quantity</div>
                                                <div class="wg-quantity">
                                                    <span class="input-group-btn">
                                                        <button type="button" id="minus"
                                                            class="btn-quantity btn-decrease">-</button>
                                                    </span>
                                                    <input type="text" name="quantity" id="quantity"
                                                        class="form-control input-lg" value="1" readonly>
                                                    <span class="input-group-btn">
                                                        <button type="button" id="add"
                                                            class="btn-quantity btn-increase">+</button>
                                                    </span>
                                                    <input type="hidden" value="{{ $product->id }}" name="id">
                                                </div>
                                                <button type="submit" @disabled(! $product->isInStock())
                                                    class="tf-btn btn-fill justify-content-center fw-6 fs-16 flex-grow-1 animate-hover-btn btn-add-to-cart w-50 my-3">
                                                    <i class="icon icon-bag"></i> Add to Cart
                                                </button>
                                            </div>
                                        </form>
                                    @endunless
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="flat-spacing-17 pt_0">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="widget-tabs style-has-border">
                        <ul class="widget-menu-tab">
                            <li class="item-title active"><span class="inner">Description</span></li>
                            <li class="item-title"><span class="inner">Return Policies</span></li>
                        </ul>
                        <div class="widget-content-tab">
                            <div class="widget-content-inner active">
                                {{--
                                    Product copy is rich text written in the admin and merchant
                                    panels, so it is rendered rather than escaped. Those panels
                                    sanitise it on save, which is where the untrusted input
                                    actually enters.
                                --}}
                                {!! $product->description !!}
                                @if ($product->additional_info)
                                    <br>
                                    {!! $product->additional_info !!}
                                @endif
                            </div>
                            <div class="widget-content-inner">
                                <h6>Return Policy for Meals On Wheels</h6>
                                <p>This item is <strong>non-returnable</strong>. However, if you receive a
                                    <strong>damaged</strong>, <strong>defective</strong>, <strong>incorrect</strong>, or
                                    <strong>expired</strong> item, you can request a replacement within <strong>72
                                        hours</strong> of delivery.
                                </p>
                                <p>For incorrect items, a replacement or return request can only be made if the item is
                                    <strong>sealed</strong>, <strong>unopened</strong>, <strong>unused</strong>, and in
                                    its <strong>original condition</strong>.
                                </p>
                                <p>Please contact our customer support team to initiate a replacement request.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(function() {
            var max = {{ (int) config('marketplace.max_quantity_per_item') }};

            $('#add').click(function(e) {
                e.preventDefault();
                var quantity = parseInt($('#quantity').val(), 10);

                if (quantity < max) {
                    $('#quantity').val(quantity + 1);
                } else {
                    alert('Maximum of ' + max + ' products allowed per order!');
                }
            });

            $('#minus').click(function(e) {
                e.preventDefault();
                var quantity = parseInt($('#quantity').val(), 10);

                if (quantity > 1) {
                    $('#quantity').val(quantity - 1);
                }
            });
        });
    </script>
@endpush
