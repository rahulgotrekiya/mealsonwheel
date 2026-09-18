<x-mail::message>
# Thanks for your order

Hello {{ $order->user->firstname }}, we have your order and will start getting it ready.

**Order:** #{{ $order->id }}
**Reference:** {{ $order->transaction_id }}
**Placed:** {{ $order->created_at->format('F j, Y') }}

<x-mail::table>
| Item | Qty | Price | Total |
|:-----|:---:|------:|------:|
@foreach ($order->items as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | ₹{{ number_format($item->unit_price, 2) }} | ₹{{ number_format($item->subtotal, 2) }} |
@endforeach
| | | **Total** | **₹{{ number_format($order->total_amount, 2) }}** |
</x-mail::table>

<x-mail::button :url="route('orders.show', $order)">
View your order
</x-mail::button>

Thanks,<br>
Meals on Wheels
</x-mail::message>
