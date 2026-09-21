@extends('layouts.panel')

@section('title', 'Edit Product')

@section('content')
    @if ($product->reject_reason)
        <div class="alert alert-danger">
            <strong>Sent back:</strong> {{ $product->reject_reason }}
        </div>
    @endif

    <div class="alert alert-info">
        Saving changes sends this listing back for review. Stock levels are changed on the
        <a href="{{ route('merchant.stock.index') }}">Stock</a> page and do not affect its status.
    </div>

    <form method="POST" action="{{ route('merchant.products.update', $product) }}" enctype="multipart/form-data"
        autocomplete="off">
        @csrf
        @method('PUT')
        @include('partials.panel.product-form', [
            'submitLabel' => 'Save and Resubmit',
            'cancelUrl' => route('merchant.products.index'),
            // Stock lives on its own page, so it is not part of a review resubmission.
            'showStock' => false,
        ])
    </form>
@endsection

@push('scripts')
    @include('partials.panel.product-form-scripts')
@endpush
