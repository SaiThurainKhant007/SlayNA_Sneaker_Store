<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\SavedCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavedCardController extends Controller
{
    public function index(Request $request): View
    {
        $cards = $request->user()->savedCards()->latest()->get();

        return view('account.saved_cards', compact('cards'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'card_holder' => ['required', 'string', 'max:100'],
            'card_number' => ['required', 'string', 'min:12'],
            'card_type' => ['required', 'in:Visa,Mastercard'],
            'expiry_month' => ['required', 'integer', 'min:1', 'max:12'],
            'expiry_year' => ['required', 'integer', 'min:'.date('Y')],
        ]);

        $digits = preg_replace('/\D/', '', $validated['card_number']);

        if (strlen($digits) < 12) {
            return back()->withErrors(['card_number' => 'Please enter valid card details.'])->withInput();
        }

        $last4 = substr($digits, -4);
        unset($digits); // discard full number immediately, never persisted

        $request->user()->savedCards()->create([
            'card_holder' => $validated['card_holder'],
            'card_last4' => $last4,
            'card_type' => $validated['card_type'],
            'expiry_month' => $validated['expiry_month'],
            'expiry_year' => $validated['expiry_year'],
        ]);

        return redirect()->route('account.saved-cards')->with('success', 'Card saved.');
    }

    public function destroy(Request $request, SavedCard $savedCard): RedirectResponse
    {
        if ($savedCard->customer_id !== $request->user()->id) {
            abort(403);
        }

        $savedCard->delete();

        return redirect()->route('account.saved-cards')->with('success', 'Card removed.');
    }
}
