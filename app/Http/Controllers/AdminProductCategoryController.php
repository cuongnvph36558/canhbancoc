<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AdminProductCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $categories = Category::query()
            ->withCount('products')
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($validated['status'] ?? null, function (Builder $query, string $status): void {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('position')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Category::count(),
            'active' => Category::where('is_active', true)->count(),
            'inactive' => Category::where('is_active', false)->count(),
            'used' => Category::has('products')->count(),
        ];

        return view('admin.categories.index', [
            'categories' => $categories,
            'stats' => $stats,
            'search' => $validated['search'] ?? '',
            'status' => $validated['status'] ?? '',
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', 'unique:categories,slug'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:8'],
            'position' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['slug'] = $validated['slug'] ?? str()->slug($validated['name']);
        $validated['icon'] = $validated['icon'] !== '' ? $validated['icon'] : null;
        $validated['position'] = $validated['position'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        try {
            Category::create($validated);
        } catch (Throwable $exception) {
            return back()->withInput()->with('error', 'Không thể tạo phân loại. Vui lòng thử lại.');
        }

        return redirect()->route('admin.categories.index')->with('success', 'Phân loại đã được tạo thành công.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', 'unique:categories,slug,'.$category->id],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:8'],
            'position' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['slug'] = $validated['slug'] ?? $category->slug;
        $validated['icon'] = $validated['icon'] !== '' ? $validated['icon'] : null;
        $validated['position'] = $validated['position'] ?? $category->position;
        $validated['is_active'] = $validated['is_active'] ?? false;

        try {
            $category->update($validated);
        } catch (Throwable $exception) {
            return back()->withInput()->with('error', 'Không thể cập nhật phân loại. Vui lòng thử lại.');
        }

        return redirect()->route('admin.categories.index')->with('success', 'Phân loại đã được cập nhật.');
    }

    public function toggleStatus(Category $category): RedirectResponse
    {
        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        return back()->with('success', 'Trạng thái phân loại đã được cập nhật.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Không thể xóa phân loại đang có sản phẩm. Hãy chuyển các sản phẩm sang phân loại khác trước.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Phân loại đã được xóa.');
    }
}
