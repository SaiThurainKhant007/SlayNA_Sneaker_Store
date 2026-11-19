<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Admin: feedback customers have sent from the Contact Us page. */
class FeedbackController extends Controller
{
    public function index(): View
    {
        $feedback = Feedback::with('customer')->latest()->get();

        return view('admin.feedback', compact('feedback'));
    }

    public function destroy(Feedback $feedback): RedirectResponse
    {
        $feedback->delete();

        return redirect()->route('admin.feedback.index')->with('success', 'Feedback deleted.');
    }
}
