@extends('layouts.admin')

@section('title', 'Tạo sản phẩm mới')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">QUẢN LÝ KHO</span>
            <h1>Tạo sản phẩm mới</h1>
            <p>Thêm một sản phẩm mới vào hệ thống của bạn.</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="admin-btn-secondary">← Quay lại</a>
    </div>

    @if ($errors->any())
        <div class="admin-alert admin-alert-error" role="alert">
            <strong>Có lỗi xảy ra:</strong>
            <ul style="margin: 8px 0 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="admin-form-panel">
        <form method="POST" action="{{ route('admin.products.store') }}" class="admin-form" enctype="multipart/form-data">
            @csrf

            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label for="name">Tên sản phẩm <span class="admin-required">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="VD: Cốc gốm cao cấp">
                </div>
                <div class="admin-form-group">
                    <label for="sku">Mã SKU <span class="admin-required">*</span></label>
                    <input type="text" id="sku" name="sku" value="{{ old('sku') }}" required placeholder="VD: SKU-001" pattern="[A-Z0-9\-]+">
                </div>
                <div class="admin-form-group">
                    <label for="category_id">Phân loại</label>
                    <select id="category_id" name="category_id">
                        <option value="">-- Chọn phân loại --</option>
                        @foreach (($categories ?? collect()) as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                {{ $category->icon ? $category->icon.' ' : '' }}{{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <small>Nếu chưa có, vào mục <em>Phân loại sản phẩm</em> để tạo trước.</small>
                </div>
            </div>

            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label for="price">Giá (₫) <span class="admin-required">*</span></label>
                    <input type="number" id="price" name="price" value="{{ old('price') }}" min="0" step="1000" required placeholder="VD: 150000">
                </div>
                <div class="admin-form-group">
                    <label for="stock">Tồn kho <span class="admin-required">*</span></label>
                    <input type="number" id="stock" name="stock" value="{{ old('stock', 0) }}" min="0" required placeholder="VD: 10">
                </div>
            </div>

            <div class="admin-form-group">
                <label for="discount_percentage">Phần trăm giảm giá (%)</label>
                <input type="number" id="discount_percentage" name="discount_percentage" value="{{ old('discount_percentage', 0) }}" min="0" max="100" step="1" placeholder="VD: 20">
                <small>Nhập từ 0 đến 100. Giá bán sau giảm sẽ được tự động tính.</small>
            </div>

            <div class="admin-form-group">
                <label for="description">Mô tả ngắn</label>
                <textarea id="description" name="description" placeholder="Mô tả sản phẩm một dòng" rows="2" maxlength="500">{{ old('description') }}</textarea>
                <small>Tối đa 500 ký tự</small>
            </div>

            <div class="admin-form-group">
                <label for="detailed_description">Mô tả chi tiết</label>
                <textarea id="detailed_description" name="detailed_description" placeholder="Mô tả đầy đủ về sản phẩm, chất liệu, công dụng..." rows="6">{{ old('detailed_description') }}</textarea>
                <small>Có thể để trống</small>
            </div>

            <div class="admin-form-group">
                <label for="images">Tải nhiều hình ảnh</label>
                <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
                <small>Chọn tối đa 10 ảnh JPG, PNG hoặc WebP; mỗi ảnh tối đa 5 MB.</small>
            </div>

            <div class="admin-form-group">
                <label for="image_url">Hoặc dùng URL ảnh</label>
                <input type="url" id="image_url" name="image_url" value="{{ old('image_url') }}" placeholder="https://example.com/image.jpg">
                <small>Ảnh tải lên sẽ được ưu tiên làm ảnh đại diện.</small>
            </div>

            <div class="admin-form-group admin-checkbox">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
                <label for="is_active">Sản phẩm đang bán</label>
                <small>Bỏ chọn để ẩn sản phẩm này trên cửa hàng</small>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn-primary">Tạo sản phẩm</button>
                <a href="{{ route('admin.products.index') }}" class="admin-btn-secondary">Hủy</a>
            </div>
        </form>
    </section>
@endsection

<style>
.admin-form-panel {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    max-width: 800px;
}

.admin-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.admin-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.admin-form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.admin-form-group label {
    font-weight: 600;
    font-size: 14px;
    color: #333;
}

.admin-required {
    color: #d32f2f;
}

.admin-form-group input,
.admin-form-group textarea {
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-family: inherit;
    font-size: 14px;
    transition: border-color 0.2s;
}

.admin-form-group input:focus,
.admin-form-group textarea:focus {
    outline: none;
    border-color: #2e7d32;
    box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
}

.admin-form-group small {
    font-size: 12px;
    color: #666;
}

.admin-form-group.admin-checkbox {
    flex-direction: row;
    gap: 10px;
}

.admin-form-group.admin-checkbox input {
    width: 18px;
    height: 18px;
    margin-top: 2px;
}

.admin-form-group.admin-checkbox label {
    font-weight: 500;
}

.admin-form-actions {
    display: flex;
    gap: 12px;
    margin-top: 10px;
}

.admin-btn-secondary {
    padding: 12px 20px;
    background: #f0f0f0;
    color: #333;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: background 0.2s;
    display: inline-block;
    border: none;
    cursor: pointer;
}

.admin-btn-secondary:hover {
    background: #e0e0e0;
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
    border: none;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: background 0.2s;
    display: inline-block;
    cursor: pointer;
}

.admin-btn-primary:hover {
    background: #1b5e20;
}
</style>
