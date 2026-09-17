@php($url = route('product', $product))
<div class="card-product style-9" data-price="{{ $product->price }}">
    <div class="card-product-wrapper">
        <a href="{{ $url }}" class="product-img">
            <img class="lazyload img-product" data-src="{{ asset($product->primary_image) }}"
                src="{{ asset($product->primary_image) }}" alt="{{ $product->name }}">
            <img class="lazyload img-hover" data-src="{{ asset($product->primary_image) }}"
                src="{{ asset($product->primary_image) }}" alt="{{ $product->name }}">
        </a>
    </div>
    <div class="card-product-info">
        <a href="{{ $url }}" class="title link">{!! $titleHtml ?? e($product->name) !!}</a>
        <span class="price">&#8377; {{ number_format($product->price, 2) }}</span>
    </div>
    <a href="{{ $url }}"
        class="fade-item fade-item-3 rounded-full tf-btn btn-fill animate-hover-btn btn-sm radius-3">
        <span>View Item</span>
    </a>
</div>
