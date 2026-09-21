@extends('layouts.panel')

@section('title', 'Add Product')

@section('content')
    <div class="alert alert-info">
        New listings are reviewed by an administrator before they appear in the shop.
    </div>

    <form method="POST" action="{{ route('merchant.products.store') }}" enctype="multipart/form-data"
        autocomplete="off">
        @csrf
        @include('partials.panel.product-form', [
            'submitLabel' => 'Submit for Review',
            'cancelUrl' => route('merchant.products.index'),
        ])
    </form>
@endsection

@push('scripts')
    @include('partials.panel.product-form-scripts')
@endpush
