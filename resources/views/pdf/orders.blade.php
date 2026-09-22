@extends('pdf.layout')

@section('title', 'Orders Report')

@section('content')
    <table class="plain">
        <tr>
            <td><span class="muted">Orders listed</span> <strong>{{ $orders->count() }}</strong></td>
            <td class="num"><span class="muted">Value</span>
                <strong>&#8377; {{ number_format($orders->sum('total_amount'), 2) }}</strong></td>
        </tr>
    </table>

    <table class="data" style="margin-top:14px">
        <thead>
            <tr>
                <th>#</th>
                <th>Reference</th>
                <th>Customer</th>
                <th>Placed</th>
                <th>Status</th>
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->transaction_id }}</td>
                    <td>{{ $order->user->full_name }}</td>
                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                    <td>{{ $order->status->label() }}</td>
                    <td class="num">&#8377; {{ number_format($order->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No orders.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">Total</th>
                <th class="num">&#8377; {{ number_format($orders->sum('total_amount'), 2) }}</th>
            </tr>
        </tfoot>
    </table>
@endsection

@section('footnote', 'Every order on record, whatever its status. Cancelled and returned orders are listed but do not count as revenue.')
