@extends('layouts.panel')

@section('title', 'Edit Category')

@section('content')
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('partials.panel.category-form', ['submitLabel' => 'Save Changes'])
    </form>
@endsection
