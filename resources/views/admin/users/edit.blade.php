@extends('layouts.panel')

@section('title', 'Edit User')

@section('content')
    <form method="POST" action="{{ route('admin.users.update', $user) }}" autocomplete="off">
        @csrf
        @method('PUT')
        @include('partials.panel.user-form', ['submitLabel' => 'Save Changes'])
    </form>
@endsection
