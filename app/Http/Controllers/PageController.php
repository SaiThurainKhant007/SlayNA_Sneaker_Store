<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    /** Brand directory: each brand links to the shop filtered by that brand. */
    public function brands(): View
    {
        $brands = Brand::withCount(['products' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('name')
            ->get();

        return view('pages.brands', compact('brands'));
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        // Latest written reviews; only the rating and text are shown on the page
        $reviews = Review::whereNotNull('comment')
            ->where('comment', '!=', '')
            ->latest()
            ->orderByDesc('id')
            ->take(4)
            ->get(['rating', 'comment']);

        return view('pages.contact', compact('reviews'));
    }

    /** Contact Us: a customer sends feedback about the store. */
    public function storeFeedback(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $request->user()->feedback()->create($validated);

        return redirect()->route('contact')->with('success', 'Thank you! Your feedback has been sent.');
    }
}
