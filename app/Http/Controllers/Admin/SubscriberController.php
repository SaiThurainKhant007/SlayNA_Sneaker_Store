<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DropAlertList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Admin: emails signed up for drop alerts in the footer (download as CSV from Reports). */
class SubscriberController extends Controller
{
    public function index(DropAlertList $list): View
    {
        $subscribers = $list->all();

        return view('admin.subscribers', compact('subscribers'));
    }

    public function destroy(Request $request, DropAlertList $list): RedirectResponse
    {
        $validated = $request->validate(['email' => ['required', 'string', 'max:255']]);
        $list->remove($validated['email']);

        return redirect()->route('admin.subscribers.index')->with('success', 'Subscriber removed.');
    }
}
