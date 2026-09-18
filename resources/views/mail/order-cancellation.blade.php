<x-mail::message>
# Your order has been cancelled

Hello {{ $order->user->firstname }}, order **#{{ $order->id }}** has been cancelled as requested.

**Reference:** {{ $order->transaction_id }}
**Order total:** ₹{{ number_format($order->total_amount, 2) }}

Nothing further is needed from you. If this was not you, please get in touch.

<x-mail::button :url="route('shop')">
Continue shopping
</x-mail::button>

Thanks,<br>
Meals on Wheels
</x-mail::message>
