@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
    <div class="card narrow">
        <h1>Sign in</h1>
        <p class="sub">Welcome back.</p>

        @error('email')
            <div class="alert alert-error">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password">
            @error('password')<div class="field-error">{{ $message }}</div>@enderror

            <label style="font-weight:400;display:flex;align-items:center;gap:7px;margin-top:16px">
                <input type="checkbox" name="remember" value="1" style="width:auto"> Remember me
            </label>

            <button type="submit">Sign in</button>
        </form>

        <p class="foot">No account? <a href="{{ route('register') }}">Create one</a></p>
    </div>
@endsection
