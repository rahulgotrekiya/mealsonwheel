@extends('layouts.panel')

@section('title', 'Dashboard')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Welcome, {{ auth()->user()->full_name }}</h5>
            <p class="text-muted mb-0">
                You supply {{ auth()->user()->products()->count() }} products.
            </p>
        </div>
    </div>
@endsection
