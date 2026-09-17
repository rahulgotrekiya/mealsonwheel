@extends('layouts.app')

@section('title', 'Merchant')

@section('content')
    <div class="card">
        <h1>Merchant panel</h1>
        <p class="sub">Signed in as {{ auth()->user()->full_name }}.</p>

        <table>
            <tr><th>Role</th><td>{{ auth()->user()->role->label() }}</td></tr>
            <tr><th>Status</th><td>{{ auth()->user()->status->label() }}</td></tr>
            <tr><th>Products supplied</th><td>{{ auth()->user()->products()->count() }}</td></tr>
        </table>
    </div>
@endsection
