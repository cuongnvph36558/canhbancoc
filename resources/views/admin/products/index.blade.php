@extends('layouts.admin')

@section('title', 'Quản lý sản phẩm')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">QUẢN LÝ KHO</span>
            <h1>Sản phẩm & kho hàng</h1>
            <p>Quản lý danh sách sản phẩm, giá, kho hàng và trạng thái hoạt động.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="admin-btn-primary">+ Thêm sản phẩm mới</a>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success" role="status">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="admin-alert admin-alert-error" role="alert">{{ session('error') }}</div>
    @endif

    <div class="admin-summary">
        <div class="admin-stat-card">
            <span>Tổng sản phẩm</span>
            <strong>{{ $stats['total'] }}</strong>
            <small>Tất cả sản phẩm trong hệ thống</small>
        </div>
        <div class="admin-stat-card">
            <span>Đang bán</span>
            <strong>{{ $stats['active'] }}</strong>
            <small>Sản phẩm hoạt động</small>
        </div>
        <div class="admin-stat-card">
            <span>Đã ẩn</span>
            <strong>{{ $stats['inactive'] }}</strong>
            <small>Sản phẩm không hiển thị</small>
        </div>
        <div class="admin-stat-card">
            <span>Tồn kho thấp</span>
            <strong>{{ $stats['low_stock'] }}</strong>
            <small>Dưới 5 sản phẩm</small>
        </div>
    </div>

    <section class="admin-products-panel" aria-labelledby="products-heading">
        <div class="admin-panel-heading">
            <div>
                <h2 id="products-heading">Danh sách sản phẩm</h2>
                <p>Quản lý, cập nhật hoặc xóa sản phẩm từ danh sách.</p>
            </div>
            <form class="admin-search" method="GET" action="{{ route('admin.products.index') }}">
                <input type="search" name="search" value="{{ $search }}" placeholder="Tìm tên hoặc SKU" aria-label="Tìm theo tên hoặc SKU">
                <select name="status" aria-label="Lọc theo trạng thái">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" @selected($status === 'active')>Đang bán</option>
                    <option value="inactive" @selected($status === 'inactive')>Đã ẩn</option>
                </select>
                <button type="submit">Tìm kiếm</button>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>SKU</th>
                        <th>Phân loại</th>
                        <th>Màu sắc</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                        <th class="admin-actions-heading">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <div class="admin-product-cell">
                                    @if ($product->primaryImage || $product->image_url)
                                        <img src="{{ $product->primaryImage?->url() ?? $product->image_url }}" alt="{{ $product->name }}" class="admin-product-thumb">
                                    @else
                                        <div class="admin-product-thumb-placeholder">📦</div>
                                    @endif
                                    <span>
                                        <strong>{{ $product->name }}</strong>
                                        <small>{{ Str::limit($product->description, 50) }}</small>
                                    </span>
                                </div>
                            </td>
                            <td><code>{{ $product->sku }}</code></td>
                            <td>
                                @if ($product->category)
                                    <span class="admin-category-chip">
                                        {{ $product->category->icon ? $product->category->icon.' ' : '' }}{{ $product->category->name }}
                                    </span>
                                @else
                                    <span style="color:#999;">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($product->colors->isNotEmpty())
                                    <div class="admin-color-swatches" aria-label="Các màu của sản phẩm">
                                        @foreach ($product->colors as $color)
                                            <span
                                                class="admin-color-swatch"
                                                style="background-color: {{ $color->hex_code }};"
                                                title="{{ $color->name }} ({{ $color->hex_code }})"
                                            ></span>
                                        @endforeach
                                    </div>
                                    <small class="admin-color-count">{{ $product->colors->count() }} màu</small>
                                @else
                                    <span style="color:#999;">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($product->discount_percentage > 0)
                                    <span class="admin-price-original">{{ number_format($product->price, 0, ',', '.') }} ₫</span>
                                    <span class="admin-price">{{ number_format($product->discountedPrice(), 0, ',', '.') }} ₫</span>
                                    <small class="admin-discount-label">-{{ $product->discount_percentage }}%</small>
                                @else
                                    <span class="admin-price">{{ number_format($product->price, 0, ',', '.') }} ₫</span>
                                @endif
                            </td>
                            <td>
                                <span @class(['admin-stock', 'is-low' => $product->stock < 5, 'is-out' => $product->stock == 0])>
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td>
                                <span @class(['admin-state', 'is-active' => $product->is_active, 'is-inactive' => ! $product->is_active])>
                                    <span aria-hidden="true"></span>
                                    {{ $product->is_active ? 'Đang bán' : 'Đã ẩn' }}
                                </span>
                            </td>
                            <td>
                                <div class="admin-row-actions">
                                    <a href="{{ route('admin.products.show', $product) }}" class="admin-action-button">Xem chi tiết</a>

                                    <a href="{{ route('admin.products.edit', $product) }}" class="admin-action-button is-secondary">Chỉnh sửa</a>

                                    <form method="POST" action="{{ route('admin.products.toggle-status', $product) }}" class="admin-inline-form">
                                        @csrf
                                        <button class="admin-action-button is-secondary" type="submit">
                                            {{ $product->is_active ? 'Ẩn' : 'Hiển thị' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="admin-action-button is-danger" type="submit">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="admin-empty-state">
                                {{ $search || $status ? 'Không tìm thấy sản phẩm phù hợp.' : 'Chưa có sản phẩm nào. Hãy tạo sản phẩm đầu tiên.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            {{ $products->links() }}
        </div>
    </section>
@endsection

<style>
.admin-product-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.admin-product-thumb {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
}

.admin-product-thumb-placeholder {
    width: 40px;
    height: 40px;
    display: grid;
    place-items: center;
    background: #f0f0f0;
    border-radius: 4px;
    font-size: 20px;
}

.admin-stock {
    padding: 4px 8px;
    background: #e8f5e9;
    color: #2e7d32;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.admin-stock.is-low {
    background: #fff3e0;
    color: #e65100;
}

.admin-stock.is-out {
    background: #ffebee;
    color: #c62828;
}

.admin-price {
    color: #1976d2;
    font-weight: 600;
    font-size: 14px;
}

.admin-price-original {
    display: block;
    color: #777;
    font-size: 12px;
    text-decoration: line-through;
}

.admin-discount-label {
    display: block;
    color: #c62828;
    font-size: 12px;
    font-weight: 600;
}

.admin-category-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    background: #e3f2fd;
    color: #1565c0;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.admin-color-swatches {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
    max-width: 160px;
}

.admin-color-swatch {
    display: inline-block;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 1px solid rgba(0, 0, 0, .15);
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, .12);
    cursor: help;
}

.admin-color-count {
    display: block;
    margin-top: 4px;
    color: #6b7280;
    font-size: 11px;
}

.admin-inline-form {
    display: contents;
}

.admin-page-heading {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
}

.admin-btn-primary {
    padding: 12px 20px;
    background: #2e7d32;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: background 0.2s;
    display: inline-block;
}

.admin-btn-primary:hover {
    background: #1b5e20;
}
</style>
