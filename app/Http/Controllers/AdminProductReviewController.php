<?php

namespace App\Http\Controllers;

use App\Models\ProductReview;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminProductReviewController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $search = $validated['search'] ?? null;
        $rating = $validated['rating'] ?? null;

        $reviews = ProductReview::query()
            ->with(['product:id,name,slug', 'user:id,name,email'])
            ->when($rating !== null, function (Builder $query) use ($rating): void {
                $query->where('rating', $rating);
            })
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('comment', 'like', "%{$search}%")
                        ->orWhereHas('product', function (Builder $query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function (Builder $query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.product-reviews.index', [
            'reviews' => $reviews,
            'search' => $validated['search'] ?? '',
            'selectedRating' => $rating ?? '',
        ]);
    }

    public function destroy(ProductReview $productReview): RedirectResponse
    {
        $productReview->delete();

        return back()->with('success', 'Đánh giá đã được xóa khỏi hệ thống.');
    }
}
