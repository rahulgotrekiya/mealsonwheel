@extends('pdf.layout')

@section('title', 'Merchant Earnings')

@section('content')
    <table class="data">
        <thead>
            <tr>
                <th>Supplier</th>
                <th>Email</th>
                <th class="num">Units</th>
                <th class="num">Gross</th>
                <th class="num">Commission</th>
                <th class="num">Owed</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($perSeller as $row)
                <tr>
                    <td>{{ $row->firstname }} {{ $row->lastname }}</td>
                    <td>{{ $row->email }}</td>
                    <td class="num">{{ number_format($row->units) }}</td>
                    <td class="num">&#8377; {{ number_format($row->gross, 2) }}</td>
                    <td class="num">&#8377; {{ number_format($row->commission, 2) }}</td>
                    <td class="num">&#8377; {{ number_format($row->net, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No supplier sales yet.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Total</th>
                <th class="num">{{ number_format($perSeller->sum('units')) }}</th>
                <th class="num">&#8377; {{ number_format($perSeller->sum('gross'), 2) }}</th>
                <th class="num">&#8377; {{ number_format($perSeller->sum('commission'), 2) }}</th>
                <th class="num">&#8377; {{ number_format($perSeller->sum('net'), 2) }}</th>
            </tr>
        </tfoot>
    </table>
@endsection

@section('footnote', 'Nothing is recorded as settled, so the owed column is everything each supplier has earned to date. Cancelled and returned orders are excluded.')
