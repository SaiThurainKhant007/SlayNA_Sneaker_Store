<?php

namespace App\Http\Controllers;

use App\Models\Brand;
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
        return view('pages.contact');
    }
}
