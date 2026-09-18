@extends('layouts.panel')

@section('title', 'Add Category')

@section('content')
    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
        @csrf
        @include('partials.panel.category-form', ['category' => null, 'submitLabel' => 'Add Category'])
    </form>
@endsection
