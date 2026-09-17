@extends('layouts.shop')

@section('title', $keyword !== '' ? 'Search: '.$keyword : 'Search')

@section('content')
    <section class="flat-spacing-8 pb-0">
        <div class="container">
            <div class="row">
                <div>
                    <div class="flat-title flex-row justify-content-between px-0 mb-4">
                        <span class="title wow fadeInUp" data-wow-delay="0s">
                            @if ($products->total() < 1)
                                No Results Found For: {{ $keyword }}
                            @else
                                Search Result For: {{ $keyword }} ({{ $products->total() }} products)
                            @endif
                        </span>
                    </div>

                    @if ($products->total() > 0)
                        <div class="filter-container mb-4 p-4 bg-light rounded shadow-sm">
                            <form method="GET" action="{{ route('search') }}" id="filter-form">
                                <input type="hidden" name="keyword" value="{{ $keyword }}">

                                <div class="col d-flex justify-content-between">
                                    <div class="col-md-6 pe-4 mb-3">
                                        <div class="form-group">
                                            <label class="fw-bold pr-2 mb-2">Sort By:</label>
                                            <select name="sort" class="form-select"
                                                onchange="document.getElementById('filter-form').submit()">
                                                <option value="default" @selected($sort === 'default')>Default</option>
                                                <option value="price_asc" @selected($sort === 'price_asc')>Price: Low to High</option>
                                                <option value="price_desc" @selected($sort === 'price_desc')>Price: High to Low</option>
                                                <option value="name_asc" @selected($sort === 'name_asc')>Name: A-Z</option>
                                                <option value="name_desc" @selected($sort === 'name_desc')>Name: Z-A</option>
                                            </select>
                                        </div>
                                        <div class="d-flex mt-4">
                                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                                            <a href="{{ route('search', ['keyword' => $keyword]) }}"
                                                class="btn btn-outline-secondary ms-2">Reset Filters</a>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="fw-bold mb-2">Price Range:</label>
                                            <div class="wrapper">
                                                <div class="price-input">
                                                    <div class="field">
                                                        <span>Min</span>
                                                        <input type="number" name="min_price" class="input-min"
                                                            value="{{ (int) $minPrice }}">
                                                    </div>
                                                    <div class="separator">-</div>
                                                    <div class="field">
                                                        <span>Max</span>
                                                        <input type="number" name="max_price" class="input-max"
                                                            value="{{ (int) $maxPrice }}">
                                                    </div>
                                                </div>
                                                <div class="slider">
                                                    <div class="progress"></div>
                                                </div>
                                                <div class="range-input">
                                                    <input type="range" class="range-min" min="{{ $floor }}"
                                                        max="{{ $ceiling }}" value="{{ (int) $minPrice }}" step="10">
                                                    <input type="range" class="range-max" min="{{ $floor }}"
                                                        max="{{ $ceiling }}" value="{{ (int) $maxPrice }}" step="10">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="grid-layout wrapper-shop mb-4" data-grid="grid-4">
                            @foreach ($products as $product)
                                @include('shop.partials.product-card', [
                                    'titleHtml' => $keyword === ''
                                        ? e($product->name)
                                        : preg_replace('/'.preg_quote($keyword, '/').'/i', '<b>$0</b>', e($product->name)),
                                ])
                            @endforeach
                        </div>

                        {{ $products->links() }}
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
    /* Price Range Slider CSS */
    .wrapper {
        width: 100%;
        background: #fff;
        padding: 20px 25px 40px;
        border-radius: 10px;
    }

    .price-input {
        display: flex;
        width: 100%;
        margin: 15px 0 35px;
    }

    .price-input .field {
        width: 100%;
        display: flex;
        align-items: center;
    }

    .field input {
        width: 100%;
        height: 40px;
        outline: none;
        font-size: 16px;
        border-radius: 5px;
        text-align: center;
        border: 1px solid #999;
        -moz-appearance: textfield;
    }

    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }

    .price-input .separator {
        width: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .slider {
        height: 5px;
        border-radius: 5px;
        background: #ddd;
        position: relative;
    }

    .slider .progress {
        height: 5px;
        border-radius: 5px;
        background: #0d6efd;
        position: absolute;
        left: 0%;
        right: 0%;
    }

    .range-input {
        position: relative;
    }

    .range-input input {
        position: absolute;
        top: -5px;
        height: 5px;
        width: 100%;
        background: none;
        pointer-events: none;
        -webkit-appearance: none;
    }

    input[type="range"]::-webkit-slider-thumb {
        height: 17px;
        width: 17px;
        border-radius: 50%;
        pointer-events: auto;
        -webkit-appearance: none;
        background: #0d6efd;
        cursor: pointer;
    }

    input[type="range"]::-moz-range-thumb {
        height: 17px;
        width: 17px;
        border-radius: 50%;
        pointer-events: auto;
        -moz-appearance: none;
        background: #0d6efd;
        cursor: pointer;
        border: none;
    }

    .field span {
        font-size: 14px;
        margin-right: 10px;
        font-weight: 500;
    }
    </style>
@endpush

@push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const rangeInput = document.querySelectorAll(".range-input input");
        const priceInput = document.querySelectorAll(".price-input input");
        const progress = document.querySelector(".slider .progress");

        // Set initial min and max values
        const minValue = parseInt(rangeInput[0].min);
        const maxValue = parseInt(rangeInput[0].max);

        // Update progress bar and price inputs when range inputs change
        rangeInput.forEach(input => {
            input.addEventListener("input", e => {
                let minVal = parseInt(rangeInput[0].value);
                let maxVal = parseInt(rangeInput[1].value);

                if (maxVal - minVal < 10) {
                    if (e.target.className === "range-min") {
                        rangeInput[0].value = maxVal - 10;
                    } else {
                        rangeInput[1].value = minVal + 10;
                    }
                } else {
                    priceInput[0].value = minVal;
                    priceInput[1].value = maxVal;
                    progress.style.left = ((minVal - minValue) / (maxValue - minValue)) * 100 + "%";
                    progress.style.right = 100 - ((maxVal - minValue) / (maxValue - minValue)) * 100 + "%";
                }
            });
        });

        // Update range sliders when price inputs change
        priceInput.forEach(input => {
            input.addEventListener("input", e => {
                let minVal = parseInt(priceInput[0].value);
                let maxVal = parseInt(priceInput[1].value);

                if ((maxVal - minVal >= 10) && maxVal <= maxValue && minVal >= minValue) {
                    if (e.target.className === "input-min") {
                        rangeInput[0].value = minVal;
                        progress.style.left = ((minVal - minValue) / (maxValue - minValue)) * 100 + "%";
                    } else {
                        rangeInput[1].value = maxVal;
                        progress.style.right = 100 - ((maxVal - minValue) / (maxValue - minValue)) * 100 + "%";
                    }
                }
            });
        });

        // Set initial progress bar position
        let minVal = parseInt(rangeInput[0].value);
        let maxVal = parseInt(rangeInput[1].value);
        progress.style.left = ((minVal - minValue) / (maxValue - minValue)) * 100 + "%";
        progress.style.right = 100 - ((maxVal - minValue) / (maxValue - minValue)) * 100 + "%";
    });
    </script>
@endpush
