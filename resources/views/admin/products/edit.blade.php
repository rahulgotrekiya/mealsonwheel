@extends('layouts.panel')

@section('title', 'Edit Product')

@section('content')
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data"
        autocomplete="off">
        @csrf
        @method('PUT')
        @include('partials.panel.product-form', ['submitLabel' => 'Save Changes', 'cancelUrl' => route('admin.products.index')])
    </form>
@endsection

@push('scripts')
    @include('partials.panel.product-form-scripts')
@endpush
