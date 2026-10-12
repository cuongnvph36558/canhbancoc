<?php

namespace App\Http\Controllers;

use App\Models\ContactInquiry;
use App\Models\Product;
use App\Services\SitePageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function home(SitePageContent $sitePageContent): View
    {
        return view('store.home', [
            'products' => $this->storefrontProducts(),
            'pageContent' => $sitePageContent->get('home'),
        ]);
    }

    public function products(): View
    {
        return view('store.products', [
            'products' => $this->storefrontProducts(),
        ]);
    }

    public function product(string $slug): View
    {
        $products = collect($this->storefrontProducts());
        $product = $products->firstWhere('slug', $slug);

        abort_if($product === null, 404);

        $productRecord = isset($product['database_product_id'])
            ? Product::query()
                ->with([
                    'reviews' => fn ($query) => $query
                        ->with('user:id,name')
                        ->latest()
                        ->limit(10),
                ])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->findOrFail($product['database_product_id'])
            : null;

        return view('store.product', [
            'product' => $product,
            'relatedProducts' => $products->where('slug', '!=', $slug)->take(4),
            'productReviews' => $productRecord?->reviews ?? collect(),
            'reviewAverage' => $productRecord?->reviews_avg_rating,
            'reviewCount' => $productRecord?->reviews_count ?? 0,
        ]);
    }

    public function about(SitePageContent $sitePageContent): View
    {
        return view('store.about', [
            'pageContent' => $sitePageContent->get('about'),
        ]);
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

        return to_route('contact')->with('success', 'Cảm ơn bạn! Lời nhắn đã được gửi đến Cốc Mơ.');
    }

    /**
     * @return array<int, array{
     *     database_product_id?: int,
     *     slug: string,
     *     name: string,
     *     category: string,
     *     price: int|float|string,
     *     discounted_price: float,
     *     discount_percentage: int,
     *     stock: int,
     *     image: string|null,
     *     images: array<int, string>,
     *     badge: string|null,
     *     description: string,
     *     details: array<int, string>
     * }>
     */
    private function storefrontProducts(): array
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with(['images', 'primaryImage'])
            ->latest()
            ->get()
            ->map(function (Product $product): array {
                $images = $product->images
                    ->map(fn ($image): string => $image->url())
                    ->all();

                if ($images === [] && $product->image_url) {
                    $images[] = $product->image_url;
                }

                return [
                    'database_product_id' => $product->id,
                    'slug' => $product->slug,
                    'name' => $product->name,
                    'category' => 'Sản phẩm',
                    'price' => $product->price,
                    'discounted_price' => $product->discountedPrice(),
                    'discount_percentage' => $product->discount_percentage,
                    'stock' => $product->stock,
                    'image' => $product->primaryImage?->url() ?? $images[0] ?? null,
                    'images' => $images,
                    'badge' => null,
                    'description' => $product->description ?? '',
                    'details' => $product->detailed_description
                        ? array_values(array_filter(preg_split('/\r\n|\r|\n/', $product->detailed_description) ?: []))
                        : [],
                ];
            })
            ->all();

        return $products !== [] ? $products : config('store.products');
    }
}
