@extends('pdf.layout')

@section('title', 'Invoice')

@section('content')
    <table class="plain">
        <tr>
            <td style="width:50%">
                <strong>Billed to</strong><br>
                {{ $order->user->full_name }}<br>
                {{ $order->user->email }}<br>
                @if ($order->user->phone)
                    {{ $order->user->phone }}<br>
                @endif
                @if ($order->user->address)
                    {{ $order->user->address->street }}<br>
                    {{ $order->user->address->city }}, {{ $order->user->address->state }}
                    {{ $order->user->address->zip_code }}
                @endif
            </td>
            <td style="width:50%">
                <table class="plain">
                    <tr>
                        <td class="muted">Invoice for order</td>
                        <td><strong>#{{ $order->id }}</strong></td>
                    </tr>
                    <tr>
                        <td class="muted">Payment reference</td>
                        <td>{{ $order->transaction_id }}</td>
                    </tr>
                    <tr>
                        <td class="muted">Order placed</td>
                        <td>{{ $order->created_at->format('j F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="muted">Status</td>
                        <td>{{ $order->status->label() }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <h2>Items</h2>

    <table class="data">
        <thead>
            <tr>
                <th>Product</th>
                <th class="num">Qty</th>
                <th class="num">Unit price</th>
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">&#8377; {{ number_format($item->unit_price, 2) }}</td>
                    <td class="num">&#8377; {{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="num">Total paid</th>
                <th class="num">&#8377; {{ number_format($order->total_amount, 2) }}</th>
            </tr>
        </tfoot>
    </table>
@endsection

@section('footnote', 'Prices shown are those charged when the order was placed. Thank you for shopping with Meals on Wheels.')
