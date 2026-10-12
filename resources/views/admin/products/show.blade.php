@extends('layouts.admin')

@section('title', 'Chi tiết sản phẩm')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">CHI TIẾT SẢN PHẨM</span>
            <h1>{{ $product->name }}</h1>
            <p>Ngày tạo: {{ $product->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('admin.products.edit', $product) }}" class="admin-btn-primary">Chỉnh sửa</a>
            <a href="{{ route('admin.products.index') }}" class="admin-btn-secondary">← Quay lại</a>
        </div>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success" role="status">{{ session('success') }}</div>
    @endif

    <div class="admin-details-grid">
        <!-- Main Content -->
        <div class="admin-details-main">
            <!-- Image Section -->
            @if ($product->images->isNotEmpty() || $product->image_url)
                <div class="admin-product-image-section">
                    <h2>Hình ảnh sản phẩm</h2>
                    <div class="admin-product-gallery">
                        @foreach ($product->images as $image)
                            <figure>
                                <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?: $product->name }}" class="admin-product-image">
                                @if ($image->is_primary)
                                    <figcaption>Ảnh đại diện</figcaption>
                                @endif
                            </figure>
                        @endforeach
                        @if ($product->image_url)
                            <figure>
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="admin-product-image">
                                <figcaption>Ảnh URL cũ</figcaption>
                            </figure>
                        @endif
                    </div>
                </div>
            @else
                <div class="admin-product-image-placeholder">
                    <span>📦</span>
                    <p>Không có hình ảnh</p>
                </div>
            @endif

            <!-- Description Section -->
            <div class="admin-card">
                <h3>Mô tả</h3>
                <div class="admin-description-block">
                    @if ($product->description)
                        <p><strong>Mô tả ngắn:</strong></p>
                        <p>{{ $product->description }}</p>
                    @else
                        <p style="color: #999;">Không có mô tả ngắn</p>
                    @endif
                </div>

                @if ($product->detailed_description)
                    <div class="admin-description-block">
                        <p><strong>Mô tả chi tiết:</strong></p>
                        <p>{{ $product->detailed_description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="admin-details-sidebar">
            <!-- Price & Stock Card -->
            <div class="admin-card">
                <h3>Giá & Kho</h3>
                <div class="admin-info-row">
                    <span class="admin-label">Giá</span>
                    <span class="admin-value-price">
                        @if ($product->discount_percentage > 0)
                            <s>{{ number_format($product->price, 0, ',', '.') }} ₫</s>
                            {{ number_format($product->discountedPrice(), 0, ',', '.') }} ₫
                        @else
                            {{ number_format($product->price, 0, ',', '.') }} ₫
                        @endif
                    </span>
                </div>
                @if ($product->discount_percentage > 0)
                    <div class="admin-info-row">
                        <span class="admin-label">Giảm giá</span>
                        <span class="admin-value">{{ $product->discount_percentage }}%</span>
                    </div>
                @endif
                <div class="admin-info-row">
                    <span class="admin-label">Tồn kho</span>
                    <span @class(['admin-value', 'is-low' => $product->stock < 5, 'is-out' => $product->stock == 0])>
                        {{ $product->stock }} sản phẩm
                    </span>
                </div>
            </div>

            <!-- Status Card -->
            <div class="admin-card">
                <h3>Trạng thái</h3>
                <div class="admin-info-row">
                    <span class="admin-label">Hiển thị</span>
                    <span @class(['admin-state', 'is-active' => $product->is_active, 'is-inactive' => ! $product->is_active])>
                        <span aria-hidden="true"></span>
                        {{ $product->is_active ? 'Đang bán' : 'Đã ẩn' }}
                    </span>
                </div>
                <form method="POST" action="{{ route('admin.products.toggle-status', $product) }}" style="margin-top: 12px;">
                    @csrf
                    <button class="admin-btn-secondary" style="width: 100%;">
                        {{ $product->is_active ? '📍 Ẩn sản phẩm' : '👁️ Hiển thị sản phẩm' }}
                    </button>
                </form>
            </div>

            <!-- SKU Card -->
            <div class="admin-card">
                <h3>Thông tin kỹ thuật</h3>
                <div class="admin-info-row">
                    <span class="admin-label">Mã SKU</span>
                    <code class="admin-code">{{ $product->sku }}</code>
                </div>
                <div class="admin-info-row">
                    <span class="admin-label">Phân loại</span>
                    <span class="admin-value">
                        @if ($product->category)
                            <a href="{{ route('admin.categories.edit', $product->category) }}" class="admin-category-chip">
                                {{ $product->category->icon ? $product->category->icon.' ' : '' }}{{ $product->category->name }}
                            </a>
                        @else
                            <span style="color:#999;">Chưa phân loại</span>
                        @endif
                    </span>
                </div>
                <div class="admin-info-row">
                    <span class="admin-label">ID sản phẩm</span>
                    <code class="admin-code">#{{ $product->id }}</code>
                </div>
            </div>

            <!-- Dates Card -->
            <div class="admin-card">
                <h3>Ngày tạo & cập nhật</h3>
                <div class="admin-info-row">
                    <span class="admin-label">Tạo lúc</span>
                    <span class="admin-value">{{ $product->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="admin-info-row">
                    <span class="admin-label">Cập nhật lúc</span>
                    <span class="admin-value">{{ $product->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <!-- Delete Card -->
            <div class="admin-card admin-card-danger">
                <h3>Vùng nguy hiểm</h3>
                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác.');">
                    @csrf
                    @method('DELETE')
                    <button class="admin-btn-danger" style="width: 100%;">🗑️ Xóa sản phẩm</button>
                </form>
                <p style="margin: 12px 0 0; font-size: 12px; color: #666;">Xóa sản phẩm sẽ xóa hoàn toàn khỏi hệ thống.</p>
            </div>
        </div>
    </div>
@endsection

<style>
.admin-details-grid {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 20px;
}

.admin-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.admin-card h3 {
    margin: 0 0 15px;
    font-size: 15px;
    font-weight: 600;
    color: #333;
}

.admin-card-danger {
    border-left: 4px solid #d32f2f;
}

.admin-product-image-section {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.admin-product-image-section h2 {
    margin: 0 0 15px;
    font-size: 16px;
}

.admin-product-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
}

.admin-product-gallery figure {
    margin: 0;
}

.admin-product-gallery figcaption {
    margin-top: 6px;
    color: #666;
    font-size: 12px;
}

.admin-product-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 6px;
}

.admin-product-image-placeholder {
    background: white;
    border-radius: 8px;
    padding: 60px 20px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    color: #999;
}

.admin-product-image-placeholder span {
    display: block;
    font-size: 48px;
    margin-bottom: 10px;
}

.admin-description-block {
    padding: 15px;
    background: #f9f9f9;
    border-radius: 6px;
    margin-bottom: 15px;
}

.admin-description-block:last-child {
    margin-bottom: 0;
}

.admin-description-block p {
    margin: 0;
    font-size: 13px;
    line-height: 1.6;
    color: #555;
}

.admin-description-block p strong {
    display: block;
    margin-bottom: 8px;
    color: #333;
}

.admin-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 13px;
}

.admin-info-row:last-child {
    border-bottom: none;
}

.admin-label {
    color: #666;
    font-weight: 500;
}

.admin-value {
    color: #333;
    font-weight: 600;
}

.admin-value-price {
    color: #1976d2;
    font-weight: 700;
    font-size: 14px;
}

.admin-value.is-low {
    color: #e65100;
    background: #fff3e0;
    padding: 2px 6px;
    border-radius: 4px;
}

.admin-value.is-out {
    color: #c62828;
    background: #ffebee;
    padding: 2px 6px;
    border-radius: 4px;
}

.admin-code {
    background: #f5f5f5;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 12px;
    color: #555;
}

.admin-state {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 4px;
}

.admin-state.is-active {
    background: #e8f5e9;
    color: #2e7d32;
}

.admin-state.is-inactive {
    background: #fff3e0;
    color: #e65100;
}

.admin-state span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: block;
    background: currentColor;
}

.admin-btn-danger {
    padding: 12px 20px;
    background: #d32f2f;
    color: white;
    border-radius: 6px;
    border: none;
    font-size: 13px;
    font-weight: 600;
    transition: background 0.2s;
    cursor: pointer;
}

.admin-btn-danger:hover {
    background: #b71c1c;
}

.admin-page-heading {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
}

@media (max-width: 1024px) {
    .admin-details-grid {
        grid-template-columns: 1fr;
    }

    .admin-details-sidebar {
        order: -1;
    }
}
</style>
