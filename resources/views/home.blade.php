@extends('layouts.app')

@section('title', 'Meals on Wheels')

@section('content')
    <div class="card">
        <h1>Meals on Wheels</h1>
        <p class="sub">Pet food from trusted suppliers, delivered from our warehouse.</p>

        @auth
            <table>
                <tr><th>Signed in as</th><td>{{ auth()->user()->full_name }}</td></tr>
                <tr><th>Email</th><td>{{ auth()->user()->email }}</td></tr>
                <tr><th>Role</th><td>{{ auth()->user()->role->label() }}</td></tr>
            </table>
        @else
            <p><a href="{{ route('login') }}">Sign in</a> or <a href="{{ route('register') }}">create an account</a> to start shopping.</p>
        @endauth
    </div>
@endsection
