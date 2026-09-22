@extends('pdf.layout')

@section('title', 'Accounts Report')

@section('content')
    @foreach ($groups as $role => $users)
        <h2>{{ $role }} ({{ $users->count() }})</h2>

        <table class="data">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->full_name }}</td>
                        {{-- No password material of any kind appears in an export. --}}
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->status->label() }}</td>
                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
@endsection

@section('footnote', 'Accounts currently on the system, grouped by role.')
