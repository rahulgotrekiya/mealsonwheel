<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Mail\OrderCancellation;
use Illuminate\Support\Facades\Mail;

class SendCancellationNotice
{
    public function handle(OrderCancelled $event): void
    {
        Mail::to($event->order->user->email)->send(new OrderCancellation($event->order));
    }
}
