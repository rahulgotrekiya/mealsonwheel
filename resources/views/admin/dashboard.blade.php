@extends('layouts.shop')

@section('title', 'Admin')

@section('content')
    @include('partials.storefront.page-title', ['title' => 'Admin panel'])

    <section class="flat-spacing-11">
        <div class="container">
            <p>Signed in as {{ auth()->user()->full_name }} ({{ auth()->user()->role->label() }}).</p>
        </div>
    </section>
@endsection
