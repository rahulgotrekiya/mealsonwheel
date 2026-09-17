@extends('layouts.app')

@section('title', 'Create account')

@section('content')
    <div class="card narrow">
        <h1>Create account</h1>
        <p class="sub">Order pet food from trusted suppliers.</p>

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <div class="row">
                <div>
                    <label for="firstname">First name</label>
                    <input id="firstname" name="firstname" value="{{ old('firstname') }}" required autofocus>
                    @error('firstname')<div class="field-error">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="lastname">Last name</label>
                    <input id="lastname" name="lastname" value="{{ old('lastname') }}" required>
                    @error('lastname')<div class="field-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
            @error('email')<div class="field-error">{{ $message }}</div>@enderror

            <label for="phone">Phone <span style="font-weight:400;color:var(--muted)">(optional)</span></label>
            <input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel">
            @error('phone')<div class="field-error">{{ $message }}</div>@enderror

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password">
            @error('password')<div class="field-error">{{ $message }}</div>@enderror

            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">

            <button type="submit">Create account</button>
        </form>

        <p class="foot">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
    </div>
@endsection
