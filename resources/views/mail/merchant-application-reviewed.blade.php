<x-mail::message>
@if ($approved)
# You're approved

Hello {{ $merchant->firstname }}, your supplier account has been approved. You can sign in now and
start listing products.

Anything you add is reviewed before it reaches the storefront, and you keep your own stock levels up
to date from the same place.

<x-mail::button :url="route('login')">
Sign in
</x-mail::button>
@else
# About your application

Hello {{ $merchant->firstname }}, thank you for your interest in supplying Meals on Wheels. We are
not able to approve your application at this time.

If you think this was a mistake, reply to this message and we will take another look.
@endif

Thanks,<br>
Meals on Wheels
</x-mail::message>
