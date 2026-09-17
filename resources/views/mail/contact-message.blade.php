<x-mail::message>
# New contact form message

**From:** {{ $senderName }} ({{ $senderEmail }})

{{ $body }}

<x-mail::button :url="'mailto:'.$senderEmail">
Reply
</x-mail::button>
</x-mail::message>
