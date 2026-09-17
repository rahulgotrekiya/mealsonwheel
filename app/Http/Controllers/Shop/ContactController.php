<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('shop.pages.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // Sent from the site's own address with the visitor as reply-to.
        // Sending as the visitor would fail SPF at the receiving end.
        Mail::to(config('mail.from.address'))->send(new ContactMessage(
            $data['name'],
            $data['email'],
            $data['message'],
        ));

        return back()->with('status', 'Thanks for getting in touch. We will reply shortly.');
    }
}
