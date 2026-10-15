<?php

namespace App\Http\Controllers;

use App\Events\StorefrontProductUpdated;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $products = Product::query()
            ->with(['images', 'primaryImage', 'category', 'colors'])
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($validated['status'] ?? null, function (Builder $query, string $status): void {
                $query->where('is_active', $status === 'active');
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'inactive' => Product::where('is_active', false)->count(),
            'low_stock' => Product::where('stock', '<', 5)->count(),
        ];

        return view('admin.products.index', [
            'products' => $products,
            'stats' => $stats,
            'search' => $validated['search'] ?? '',
            'status' => $validated['status'] ?? '',
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('position')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $categoryIds = Category::pluck('id')->all();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_percentage' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'integer', Rule::in($categoryIds)],
            'detailed_description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['sometimes', 'boolean'],
            'colors' => ['nullable', 'array'],
            'colors.*.name' => ['required_with:colors', 'string', 'max:50'],
            'colors.*.hex_code' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $validated['slug'] = str()->slug($validated['name']);
        $validated['is_active'] = $validated['is_active'] ?? true;
        $imageFiles = $request->file('images', []);
        unset($validated['images']);

        $storedPaths = $this->storeImages($imageFiles);
        $colors = $validated['colors'] ?? [];
        unset($validated['colors']);

        try {
            DB::transaction(function () use ($validated, $storedPaths, $colors): void {
                $product = Product::create($validated);

                foreach ($storedPaths as $position => $path) {
                    $product->images()->create([
                        'image_url' => $path,
                        'position' => $position,
                        'is_primary' => $position === 0,
                    ]);
                }

                $this->syncProductColors($product, $colors);

                event(new StorefrontProductUpdated([
                    'id' => $product->id,
                    'slug' => $product->slug,
                    'name' => $product->name,
                    'price' => $product->price,
                    'discounted_price' => $product->discountedPrice(),
                    'discount_percentage' => $product->discount_percentage,
                    'stock' => $product->stock,
                    'is_active' => $product->is_active,
                    'updated_at' => $product->updated_at?->toDateTimeString(),
                ]));
            });
        } catch (Throwable $exception) {
            $this->deleteStoredImages($storedPaths);

            throw $exception;
        }

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được tạo thành công.');
    }

    public function show(Product $product): View
    {
        $product->load(['images', 'primaryImage', 'category']);

        return view('admin.products.show', ['product' => $product]);
    }

    public function edit(Product $product): View
    {
        $product->load(['images', 'category', 'colors']);

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('position')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $categoryIds = Category::pluck('id')->all();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,'.$product->id],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_percentage' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'integer', Rule::in($categoryIds)],
            'detailed_description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'remove_legacy_image' => ['sometimes', 'boolean'],
            'remove_image_ids' => ['nullable', 'array', 'max:10'],
            'remove_image_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('product_images', 'id')->where(
                    fn (QueryBuilder $query): QueryBuilder => $query->where('product_id', $product->id)
                ),
            ],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['sometimes', 'boolean'],
            'colors' => ['nullable', 'array'],
            'colors.*.id' => ['nullable', 'integer'],
            'colors.*.name' => ['required_with:colors', 'string', 'max:50'],
            'colors.*.hex_code' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $imageFiles = $request->file('images', []);
        $removeImageIds = $validated['remove_image_ids'] ?? [];
        $remainingCount = $product->images()->whereNotIn('id', $removeImageIds)->count();

        if ($remainingCount + count($imageFiles) > 10) {
            return back()
                ->withErrors(['images' => 'Mỗi sản phẩm chỉ được có tối đa 10 hình ảnh.'])
                ->withInput();
        }

        $validated['slug'] = str()->slug($validated['name']);
        $validated['is_active'] = $validated['is_active'] ?? true;
        if ($request->boolean('remove_legacy_image')) {
            $validated['image_url'] = null;
        }

        unset($validated['images'], $validated['remove_image_ids'], $validated['remove_legacy_image']);
        $storedPaths = $this->storeImages($imageFiles);
        $colors = $validated['colors'] ?? [];
        unset($validated['colors']);

        try {
            $removedImages = DB::transaction(function () use ($product, $validated, $removeImageIds, $storedPaths, $colors) {
                $product->update($validated);

                $removedImages = $product->images()->whereIn('id', $removeImageIds)->get();
                $removedImages->each->delete();

                $remainingImages = $product->images()->get();
                $hasPrimaryImage = $remainingImages->contains('is_primary', true);

                if (! $hasPrimaryImage && $remainingImages->isNotEmpty()) {
                    $remainingImages->first()->update(['is_primary' => true]);
                    $hasPrimaryImage = true;
                }

                $nextPosition = ((int) $remainingImages->max('position')) + 1;

                foreach ($storedPaths as $offset => $path) {
                    $product->images()->create([
                        'image_url' => $path,
                        'position' => $nextPosition + $offset,
                        'is_primary' => ! $hasPrimaryImage && $offset === 0,
                    ]);
                }

                $this->syncProductColors($product, $colors);

                event(new StorefrontProductUpdated([
                    'id' => $product->id,
                    'slug' => $product->slug,
                    'name' => $product->name,
                    'price' => $product->price,
                    'discounted_price' => $product->discountedPrice(),
                    'discount_percentage' => $product->discount_percentage,
                    'stock' => $product->stock,
                    'is_active' => $product->is_active,
                    'updated_at' => $product->updated_at?->toDateTimeString(),
                ]));

                return $removedImages;
            });
        } catch (Throwable $exception) {
            $this->deleteStoredImages($storedPaths);

            throw $exception;
        }

        $this->deleteStoredImages(
            $removedImages->pluck('image_url')
                ->filter(fn (string $path): bool => ! filter_var($path, FILTER_VALIDATE_URL))
                ->all()
        );

        return redirect()->route('admin.products.show', $product)->with('success', 'Sản phẩm đã được cập nhật.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $imagePaths = $product->images()
            ->pluck('image_url')
            ->filter(fn (string $path): bool => ! filter_var($path, FILTER_VALIDATE_URL))
            ->all();

        $product->delete();

        $this->deleteStoredImages($imagePaths);

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được xóa.');
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        $product->update([
            'is_active' => ! $product->is_active,
        ]);

        event(new StorefrontProductUpdated([
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'price' => $product->price,
            'discounted_price' => $product->discountedPrice(),
            'discount_percentage' => $product->discount_percentage,
            'stock' => $product->stock,
            'is_active' => $product->is_active,
            'updated_at' => $product->updated_at?->toDateTimeString(),
        ]));

        return back()->with('success', 'Trạng thái sản phẩm đã được cập nhật.');
    }

    /**
     * @param  array<int, array{id?: int, name: string, hex_code?: string|null}>  $colors
     */
    private function syncProductColors(Product $product, array $colors): void
    {
        $validColors = collect($colors)
            ->filter(fn (array $color): bool => ! empty($color['name']))
            ->values();

        $product->colors()->delete();

        foreach ($validColors as $color) {
            $hex = $color['hex_code'] ?? null;
            $product->colors()->create([
                'name' => $color['name'],
                'hex_code' => $hex !== null ? strtoupper($hex) : null,
                'is_active' => true,
            ]);
        }
    }

    /**
     * @param  array<int, UploadedFile>  $images
     * @return array<int, string>
     */
    private function storeImages(array $images): array
    {
        $paths = [];

        foreach ($images as $image) {
            $path = $image->store('products', 'public');

            if (! is_string($path)) {
                $this->deleteStoredImages($paths);

                throw new RuntimeException('Không thể lưu ảnh sản phẩm.');
            }

            $paths[] = $path;
        }

        return $paths;
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function deleteStoredImages(array $paths): void
    {
        if ($paths !== [] && ! Storage::disk('public')->delete($paths)) {
            throw new RuntimeException('Không thể xóa một hoặc nhiều ảnh sản phẩm khỏi bộ nhớ.');
        }
    }
}
