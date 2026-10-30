<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;


class PaymentController extends Controller
{
    public function index(): View
    {
        $payments = Payment::with(['order.customer', 'order.items'])
            ->orderByRaw("(payment_status = 'pending') DESC")
            ->latest('id')
            ->get();

        return view('staff.payments', compact('payments'));
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:confirmed,rejected'],
        ]);

        $payment->update([
            'payment_status' => $validated['decision'],
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
        ]);

        // If payment confirmed, move the order along to "confirmed"
        if ($validated['decision'] === 'confirmed' && $payment->order->order_status === 'pending') {
            $payment->order->update(['order_status' => 'confirmed']);
        }

        return redirect()->route('staff.payments.index')->with('success', "Payment {$validated['decision']}.");
    }
}
