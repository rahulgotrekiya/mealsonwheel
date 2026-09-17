@extends('layouts.shop')

@section('title', $heading)

@section('content')
    @include('partials.storefront.page-title', ['title' => $heading])

    <section class="flat-spacing-8 pb-0">
        <div class="container">
            <div class="flat-title flex-row justify-content-between px-0 mb-4">
                <span class="title wow fadeInUp" data-wow-delay="0s">{{ $heading }}</span>
            </div>

            @if ($products->isEmpty())
                <p class="mb-4">Nothing here just yet. Have a look at the
                    <a href="{{ route('shop') }}">rest of the shop</a>.
                </p>
            @else
                <div class="grid-layout wrapper-shop mb-4" data-grid="grid-4">
                    @foreach ($products as $product)
                        @include('shop.partials.product-card')
                    @endforeach
                </div>

                {{-- Renders nothing while the catalog fits on one page. --}}
                {{ $products->links() }}
            @endif
        </div>
    </section>
@endsection
