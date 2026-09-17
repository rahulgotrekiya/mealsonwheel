<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        // Signing up twice is not an error worth showing anyone.
        NewsletterSubscriber::firstOrCreate(['email' => $data['email']]);

        return back()->with('newsletter', 'Thanks for subscribing.');
    }
}
