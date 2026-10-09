<?php

namespace App\Http\Controllers;

use App\Models\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function home(): View
    {
        return view('store.home', [
            'products' => config('store.products'),
        ]);
    }

    public function products(): View
    {
        return view('store.products', [
            'products' => config('store.products'),
        ]);
    }

    public function product(string $slug): View
    {
        $products = collect(config('store.products'));
        $product = $products->firstWhere('slug', $slug);

        abort_if($product === null, 404);

        return view('store.product', [
            'product' => $product,
            'relatedProducts' => $products->where('slug', '!=', $slug)->take(4),
        ]);
    }

    public function about(): View
    {
        return view('store.about');
    }

    public function contact(): View
    {
        return view('store.contact');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactInquiry::create($validated);

        return to_route('contact')->with('success', 'Cảm ơn bạn! Lời nhắn đã được gửi đến Mộc Thiên.');
    }
}
