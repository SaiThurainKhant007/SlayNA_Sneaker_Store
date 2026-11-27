<?php

namespace App\Http\Controllers;

use App\Support\DropAlertList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Footer "Get the Drop" signup: anyone can leave an email for new-arrival and restock alerts. */
class SubscriberController extends Controller
{
    public function store(Request $request, DropAlertList $list): RedirectResponse
    {
        $validated = $request->validateWithBag('subscribe', [
            'email' => ['required', 'string', 'email', 'max:255'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        // Signing up twice is fine: the same address is only stored once
        $added = $list->add($validated['email']);

        return back()
            ->withFragment('drop-alerts')
            ->with('subscribed', $added
                ? "You're on the list! We'll email you when new pairs drop."
                : "You're already on the list. We'll email you when new pairs drop.");
    }
}
