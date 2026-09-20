@extends('layouts.panel')

@section('title', 'Add User')

@section('content')
    <form method="POST" action="{{ route('admin.users.store') }}" autocomplete="off">
        @csrf
        @include('partials.panel.user-form', ['user' => null, 'submitLabel' => 'Add User'])
    </form>
@endsection
