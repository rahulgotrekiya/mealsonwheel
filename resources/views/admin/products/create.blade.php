@extends('layouts.panel')

@section('title', 'Add Product')

@section('content')
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" autocomplete="off">
        @csrf
        @include('partials.panel.product-form', ['submitLabel' => 'Add Product', 'cancelUrl' => route('admin.products.index')])
    </form>
@endsection

@push('scripts')
    @include('partials.panel.product-form-scripts')
@endpush
