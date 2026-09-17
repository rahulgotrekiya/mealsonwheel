@extends('layouts.app')

@section('title', 'Admin')

@section('content')
    <div class="card">
        <h1>Admin panel</h1>
        <p class="sub">Signed in as {{ auth()->user()->full_name }}.</p>

        <table>
            <tr><th>Role</th><td>{{ auth()->user()->role->label() }}</td></tr>
            <tr><th>Status</th><td>{{ auth()->user()->status->label() }}</td></tr>
        </table>
    </div>
@endsection
