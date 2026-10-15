@extends('layouts.admin')

@section('title', 'Chỉnh sửa sản phẩm')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">QUẢN LÝ KHO</span>
            <h1>Chỉnh sửa sản phẩm</h1>
            <p>Cập nhật thông tin: {{ $product->name }}</p>
        </div>
        <a href="{{ route('admin.products.show', $product) }}" class="admin-btn-secondary">← Quay lại</a>
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

    @if (session('success'))
        <div class="admin-alert admin-alert-success" role="status">{{ session('success') }}</div>
    @endif

    <section class="admin-form-panel">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" class="admin-form" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Thông tin cơ bản --}}
            <div class="admin-form-section">
                <h3 class="admin-form-section-title">📦 Thông tin cơ bản</h3>
                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label for="name">Tên sản phẩm <span class="admin-required">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required placeholder="VD: Cốc gốm cao cấp">
                    </div>
                    <div class="admin-form-group">
                        <label for="sku">Mã SKU <span class="admin-required">*</span></label>
                        <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required placeholder="VD: SKU-001">
                    </div>
                    <div class="admin-form-group">
                        <label for="category_id">Phân loại</label>
                        <select id="category_id" name="category_id">
                            <option value="">-- Chọn phân loại --</option>
                            @foreach (($categories ?? collect()) as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
                                    {{ $category->icon ? $category->icon.' ' : '' }}{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Giá & Kho --}}
            <div class="admin-form-section">
                <h3 class="admin-form-section-title">💰 Giá & Kho hàng</h3>
                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label for="price">Giá bán (₫) <span class="admin-required">*</span></label>
                        <input type="number" id="price" name="price" value="{{ old('price', $product->price) }}" min="0" step="1000" required>
                    </div>
                    <div class="admin-form-group">
                        <label for="stock">Số lượng tồn kho <span class="admin-required">*</span></label>
                        <input type="number" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" min="0" required>
                    </div>
                    <div class="admin-form-group">
                        <label for="discount_percentage">Giảm giá (%)</label>
                        <input type="number" id="discount_percentage" name="discount_percentage" value="{{ old('discount_percentage', $product->discount_percentage) }}" min="0" max="100" step="1">
                        <small>0 = không giảm giá</small>
                    </div>
                </div>
            </div>

            {{-- Mô tả --}}
            <div class="admin-form-section">
                <h3 class="admin-form-section-title">📝 Mô tả sản phẩm</h3>
                <div class="admin-form-group">
                    <label for="description">Mô tả ngắn</label>
                    <textarea id="description" name="description" placeholder="Mô tả sản phẩm một dòng" rows="2" maxlength="500">{{ old('description', $product->description) }}</textarea>
                </div>
                <div class="admin-form-group">
                    <label for="detailed_description">Mô tả chi tiết</label>
                    <textarea id="detailed_description" name="detailed_description" placeholder="Mô tả đầy đủ về sản phẩm..." rows="4">{{ old('detailed_description', $product->detailed_description) }}</textarea>
                </div>
            </div>

            {{-- Hình ảnh --}}
            <div class="admin-form-section">
                <h3 class="admin-form-section-title">🖼️ Hình ảnh sản phẩm</h3>
                
                @if ($product->images->isNotEmpty())
                    <div class="admin-images-section">
                        <label class="admin-images-label">Ảnh hiện tại ({{ $product->images->count() }})</label>
                        <div class="admin-image-grid">
                            @foreach ($product->images as $image)
                                <div class="admin-image-item {{ $image->is_primary ? 'is-primary' : '' }}">
                                    <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?: $product->name }}">
                                    @if ($image->is_primary)
                                        <span class="admin-image-badge">Ảnh đại diện</span>
                                    @endif
                                    <label class="admin-image-remove">
                                        <input type="checkbox" name="remove_image_ids[]" value="{{ $image->id }}">
                                        <span>× Xóa</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="admin-form-group">
                    <label for="images" class="admin-file-upload" id="imagesUploadLabel">
                        <span class="admin-file-upload-icon">📁</span>
                        <span class="admin-file-upload-text" id="imagesUploadText">Chọn hình ảnh hoặc kéo thả vào đây</span>
                        <span class="admin-file-upload-hint">JPG, PNG, WebP • Tối đa 10 ảnh • Mỗi ảnh tối đa 5 MB</span>
                    </label>
                    <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/webp" multiple style="display: none;">
                    {{-- Preview khu vực hiển thị hình ảnh mới chọn --}}
                    <div id="imagesPreview" class="admin-images-preview" hidden>
                        <div class="admin-images-preview-header">
                            <span class="admin-images-preview-title">Ảnh mới chọn (<span id="imagesPreviewCount">0</span>)</span>
                            <button type="button" class="admin-images-clear-all" id="imagesClearAll">Xóa tất cả</button>
                        </div>
                        <div class="admin-image-grid" id="imagesPreviewGrid"></div>
                    </div>
                </div>

                <div class="admin-form-group">
                    <label for="image_url">Hoặc dùng URL ảnh</label>
                    <input type="url" id="image_url" name="image_url" value="{{ old('image_url', $product->image_url) }}" placeholder="https://example.com/image.jpg">
                    @if ($product->image_url)
                        <label class="admin-image-removal">
                            <input type="checkbox" name="remove_legacy_image" value="1">
                            Xóa ảnh URL cũ
                        </label>
                    @endif
                    {{-- Preview cho URL ảnh --}}
                    <div id="imageUrlPreview" class="admin-url-preview" {{ $product->image_url ? '' : 'hidden' }}>
                        <img id="imageUrlPreviewImg" src="{{ $product->image_url ?: '' }}" alt="Preview URL">
                        <button type="button" class="admin-url-preview-remove" id="imageUrlPreviewRemove" title="Bỏ URL">×</button>
                    </div>
                </div>
            </div>

            {{-- Màu sắc --}}
            <div class="admin-form-section">
                <h3 class="admin-form-section-title">🎨 Màu sắc sản phẩm</h3>
                <p class="admin-form-hint">Thêm các loại màu có sẵn cho sản phẩm này. Có thể chỉ cần nhập tên (không bắt buộc mã màu).</p>
                <div class="color-inputs" id="colorInputs">
                    @forelse($product->colors as $index => $color)
                        <div class="color-row" @if($color->hex_code === null) data-no-hex="true" @endif>
                            <input type="color" name="colors[{{ $index }}][hex_code]" value="{{ $color->hex_code ?? '#000000' }}" class="color-picker" data-color-picker>
                            <input type="text" name="colors[{{ $index }}][name]" value="{{ $color->name }}" placeholder="Tên màu" class="color-name" required>
                            <input type="hidden" name="colors[{{ $index }}][id]" value="{{ $color->id }}">
                            <label class="color-no-hex-toggle" title="Bỏ mã màu, chỉ cần tên">
                                <input type="checkbox" name="colors[{{ $index }}][no_hex]" value="1" data-color-no-hex onchange="toggleNoHex(this)" @checked($color->hex_code === null)>
                                <span>Chỉ cần tên</span>
                            </label>
                            <button type="button" class="color-remove-btn" onclick="removeColorRow(this)" title="Xóa màu">×</button>
                        </div>
                    @empty
                        <div class="color-row">
                            <input type="color" name="colors[0][hex_code]" value="#000000" class="color-picker" data-color-picker>
                            <input type="text" name="colors[0][name]" placeholder="Tên màu (VD: Đen, Hồng pastel...)" class="color-name" required>
                            <label class="color-no-hex-toggle" title="Bỏ mã màu, chỉ cần tên">
                                <input type="checkbox" name="colors[0][no_hex]" value="1" data-color-no-hex onchange="toggleNoHex(this)">
                                <span>Chỉ cần tên</span>
                            </label>
                            <button type="button" class="color-remove-btn" onclick="removeColorRow(this)" style="visibility: hidden;" title="Xóa màu">×</button>
                        </div>
                    @endforelse
                </div>
                <button type="button" class="admin-btn-add-color" onclick="addColorRow()">
                    <span>+</span> Thêm màu
                </button>
            </div>

            {{-- Trạng thái --}}
            <div class="admin-form-section admin-form-section-footer">
                <div class="admin-checkbox-large">
                    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
                    <label for="is_active">
                        <span class="admin-checkbox-indicator"></span>
                        Sản phẩm đang bán
                    </label>
                </div>
                <small class="admin-form-hint">Bỏ chọn để ẩn sản phẩm này trên cửa hàng</small>
            </div>

            {{-- Actions --}}
            <div class="admin-form-actions">
                <button type="submit" class="admin-btn-primary admin-btn-large">
                    <span>✓</span> Cập nhật sản phẩm
                </button>
                <a href="{{ route('admin.products.show', $product) }}" class="admin-btn-secondary admin-btn-large">Hủy</a>
            </div>
        </form>
    </section>
@endsection

<style>
/* Panel Container */
.admin-form-panel {
    background: white;
    border-radius: 12px;
    padding: 28px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    max-width: 900px;
}

.admin-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Form Section */
.admin-form-section {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding-bottom: 24px;
    border-bottom: 1px solid #f0f0f0;
}

.admin-form-section:last-of-type {
    border-bottom: none;
    padding-bottom: 0;
}

.admin-form-section-title {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Form Rows */
.admin-form-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

/* Form Groups */
.admin-form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.admin-form-group label {
    font-weight: 600;
    font-size: 13px;
    color: #374151;
}

.admin-required {
    color: #ef4444;
}

.admin-form-group input,
.admin-form-group textarea,
.admin-form-group select {
    padding: 11px 14px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    color: #1f2937;
    background: #fff;
    transition: all 0.2s;
}

.admin-form-group input:hover,
.admin-form-group textarea:hover,
.admin-form-group select:hover {
    border-color: #d1d5db;
}

.admin-form-group input:focus,
.admin-form-group textarea:focus,
.admin-form-group select:focus {
    outline: none;
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
}

.admin-form-group input::placeholder,
.admin-form-group textarea::placeholder {
    color: #9ca3af;
}

.admin-form-group small,
.admin-form-hint {
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
}

/* Checkbox */
.admin-checkbox-large {
    display: flex;
    align-items: center;
    gap: 12px;
}

.admin-checkbox-large input[type="checkbox"] {
    width: 20px;
    height: 20px;
    accent-color: #22c55e;
    cursor: pointer;
}

.admin-checkbox-large label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-weight: 500;
    font-size: 14px;
    color: #374151;
}

/* Buttons */
.admin-form-actions {
    display: flex;
    gap: 12px;
    padding-top: 8px;
}

.admin-btn-primary {
    padding: 14px 28px;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
    border-radius: 8px;
    border: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.admin-btn-primary:hover {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

.admin-btn-secondary {
    padding: 14px 24px;
    background: #f3f4f6;
    color: #374151;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1.5px solid #e5e7eb;
}

.admin-btn-secondary:hover {
    background: #e5e7eb;
    border-color: #d1d5db;
}

.admin-btn-large {
    padding: 14px 28px;
    font-size: 15px;
}

/* Page Heading */
.admin-page-heading {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 16px;
}

.admin-page-heading h1 {
    margin: 4px 0 0;
    font-size: 24px;
    color: #1f2937;
}

.admin-page-eyebrow {
    font-size: 11px;
    font-weight: 700;
    color: #22c55e;
    letter-spacing: 0.5px;
}

.admin-page-heading p {
    margin: 4px 0 0;
    color: #6b7280;
    font-size: 14px;
}

/* Color Inputs */
.color-inputs {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.color-row {
    display: flex;
    gap: 10px;
    align-items: center;
    padding: 12px 16px;
    background: #f9fafb;
    border-radius: 10px;
    border: 1.5px solid #e5e7eb;
    transition: all 0.2s;
}

.color-row:hover {
    border-color: #d1d5db;
    background: #f3f4f6;
}

.color-picker {
    width: 50px;
    height: 42px;
    min-width: 50px;
    padding: 0;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    background: #fff;
    transition: all 0.2s;
    overflow: hidden;
    position: relative;
    z-index: 1;
    -webkit-appearance: none;
    appearance: none;
}

.color-picker::-webkit-color-swatch-wrapper {
    padding: 2px;
}

.color-picker::-webkit-color-swatch {
    border: none;
    border-radius: 4px;
    min-height: 34px;
}

.color-picker::-moz-color-swatch {
    border: none;
    border-radius: 4px;
}

.color-picker:hover {
    border-color: #22c55e;
}

.color-picker:focus {
    outline: none;
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
    z-index: 2;
}

.color-name {
    flex: 1;
    padding: 10px 14px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}

.color-name:focus {
    outline: none;
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
}

/* "Chỉ cần tên" toggle */
.color-no-hex-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    background: #fef3c7;
    border: 1.5px solid #fde68a;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #92400e;
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
    transition: all 0.2s;
}

.color-no-hex-toggle:hover {
    background: #fde68a;
    border-color: #f59e0b;
}

.color-no-hex-toggle input[type="checkbox"] {
    width: 14px;
    height: 14px;
    accent-color: #f59e0b;
    cursor: pointer;
}

.color-row[data-no-hex="true"] .color-picker {
    opacity: 0.35;
    pointer-events: none;
    filter: grayscale(1);
}

.color-row[data-no-hex="true"] {
    background: #fffbeb;
    border-color: #fde68a;
}

.color-remove-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: #fee2e2;
    color: #ef4444;
    border-radius: 8px;
    cursor: pointer;
    font-size: 18px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.color-remove-btn:hover {
    background: #fecaca;
    transform: scale(1.05);
}

.admin-btn-add-color {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    background: #eff6ff;
    color: #3b82f6;
    border: 1.5px dashed #93c5fd;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.admin-btn-add-color:hover {
    background: #dbeafe;
    border-color: #3b82f6;
}

.admin-btn-add-color span:first-child {
    font-size: 16px;
    font-weight: 700;
}

/* File Upload */
.admin-file-upload {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 32px 24px;
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    background: #fafafa;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
    position: relative;
    z-index: 1;
}

.admin-file-upload:hover {
    border-color: #22c55e;
    background: #f0fdf4;
}

.admin-file-upload-icon {
    font-size: 32px;
}

.admin-file-upload-text {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.admin-file-upload-hint {
    font-size: 12px;
    color: #9ca3af;
}

/* Images Section */
.admin-images-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.admin-images-label {
    font-weight: 600;
    font-size: 13px;
    color: #374151;
}

/* Images Preview */
.admin-images-preview {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 14px;
    background: #f9fafb;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    margin-top: 4px;
}

.admin-images-preview[hidden],
.admin-url-preview[hidden] {
    display: none !important;
}

.admin-images-preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
}

.admin-images-preview-title {
    font-weight: 600;
    font-size: 13px;
    color: #374151;
}

.admin-images-clear-all {
    background: transparent;
    border: 1.5px solid #fca5a5;
    color: #dc2626;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.admin-images-clear-all:hover {
    background: #fee2e2;
    border-color: #ef4444;
}

.admin-preview-item {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid #e5e7eb;
    background: #fff;
    transition: all 0.2s;
}

.admin-preview-item:hover {
    border-color: #22c55e;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.admin-preview-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    display: block;
}

.admin-preview-item-info {
    padding: 6px 8px;
    background: #fff;
    border-top: 1px solid #f3f4f6;
    font-size: 11px;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.admin-preview-item-name {
    display: block;
    font-weight: 600;
    color: #374151;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
}

.admin-preview-item-size {
    display: block;
    color: #9ca3af;
    margin-top: 2px;
}

.admin-preview-item-remove {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.95);
    color: white;
    border: 2px solid white;
    cursor: pointer;
    font-size: 14px;
    line-height: 1;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    transition: all 0.2s;
}

.admin-preview-item-remove:hover {
    background: #dc2626;
    transform: scale(1.1);
}

.admin-preview-item.is-new {
    border-color: #22c55e;
    border-style: dashed;
}

.admin-preview-item-badge {
    position: absolute;
    top: 6px;
    left: 6px;
    background: #22c55e;
    color: white;
    font-size: 10px;
    font-weight: 600;
    padding: 3px 7px;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

/* URL preview */
.admin-url-preview {
    position: relative;
    display: inline-block;
    margin-top: 8px;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid #e5e7eb;
    background: #fff;
    max-width: 200px;
}

.admin-url-preview img {
    display: block;
    width: 100%;
    height: 140px;
    object-fit: cover;
}

.admin-url-preview-remove {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.95);
    color: white;
    border: 2px solid white;
    cursor: pointer;
    font-size: 14px;
    line-height: 1;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    transition: all 0.2s;
}

.admin-url-preview-remove:hover {
    background: #dc2626;
    transform: scale(1.1);
}

.admin-image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 16px;
}

.admin-image-item {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    border: 2px solid #e5e7eb;
    background: #f9fafb;
    transition: all 0.2s;
}

.admin-image-item:hover {
    border-color: #d1d5db;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.admin-image-item.is-primary {
    border-color: #22c55e;
}

.admin-image-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    display: block;
}

.admin-image-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #22c55e;
    color: white;
    font-size: 10px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 6px;
}

.admin-image-remove {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
    background: rgba(255, 255, 255, 0.95);
    cursor: pointer;
    font-size: 12px;
    color: #ef4444;
    font-weight: 500;
    transition: all 0.2s;
}

.admin-image-remove:hover {
    background: #fee2e2;
}

.admin-image-remove input {
    display: none;
}

.admin-image-removal {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    color: #ef4444;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    margin-top: 8px;
}

.admin-image-removal input {
    width: 16px;
    height: 16px;
    accent-color: #ef4444;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-form-row {
        grid-template-columns: 1fr;
    }

    .admin-form-panel {
        padding: 20px;
    }

    .admin-form-actions {
        flex-direction: column;
    }

    .admin-btn-primary,
    .admin-btn-secondary {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .admin-page-heading {
        flex-direction: column;
        align-items: stretch;
    }

    .color-row {
        flex-wrap: wrap;
    }

    .color-name {
        flex: 1 1 100%;
    }
}
</style>

<script>
let colorIndex = {{ $product->colors->count() > 0 ? $product->colors->count() : 1 }};

function addColorRow() {
    const container = document.getElementById('colorInputs');
    const newRow = document.createElement('div');
    newRow.className = 'color-row';
    newRow.innerHTML = `
        <input type="color" name="colors[${colorIndex}][hex_code]" value="#000000" class="color-picker" data-color-picker>
        <input type="text" name="colors[${colorIndex}][name]" placeholder="Tên màu (VD: Đen, Hồng pastel...)" class="color-name" required>
        <label class="color-no-hex-toggle" title="Bỏ mã màu, chỉ cần tên">
            <input type="checkbox" name="colors[${colorIndex}][no_hex]" value="1" data-color-no-hex onchange="toggleNoHex(this)">
            <span>Chỉ cần tên</span>
        </label>
        <button type="button" class="color-remove-btn" onclick="removeColorRow(this)" title="Xóa màu">×</button>
    `;
    container.appendChild(newRow);
    colorIndex++;
    updateRemoveButtons();
}

function removeColorRow(btn) {
    const row = btn.closest('.color-row');
    const idField = row.querySelector('input[name*="[id]"]');
    if (idField) {
        idField.value = '';
        row.style.display = 'none';
    } else {
        row.remove();
    }
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.color-row');
    const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
    const removeBtns = document.querySelectorAll('.color-remove-btn');
    removeBtns.forEach((btn) => {
        btn.style.visibility = visibleRows.length > 1 ? 'visible' : 'hidden';
    });
}

function toggleNoHex(checkbox) {
    const row = checkbox.closest('.color-row');
    const picker = row.querySelector('[data-color-picker]');
    if (checkbox.checked) {
        row.setAttribute('data-no-hex', 'true');
        if (picker) {
            picker.dataset.previousValue = picker.value;
            picker.value = '#000000';
        }
    } else {
        row.removeAttribute('data-no-hex');
        if (picker && picker.dataset.previousValue) {
            picker.value = picker.dataset.previousValue;
        }
    }
}

// Before submit: clear hex_code fields for rows marked "no_hex" so backend treats them as null
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form.admin-form');
    if (form) {
        form.addEventListener('submit', function() {
            document.querySelectorAll('.color-row').forEach(function(row) {
                const noHex = row.querySelector('[data-color-no-hex]');
                const picker = row.querySelector('[data-color-picker]');
                if (noHex && noHex.checked && picker) {
                    picker.disabled = true; // disabled inputs are excluded from form submit
                }
            });
        });
    }
});

// ============================================
// FILE UPLOAD PREVIEW - IMAGES
// ============================================

// Initialize remove buttons visibility
updateRemoveButtons();

document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    try {
    const fileInput = document.getElementById('images');
    const previewSection = document.getElementById('imagesPreview');
    const previewGrid = document.getElementById('imagesPreviewGrid');
    const previewCount = document.getElementById('imagesPreviewCount');
    const uploadText = document.getElementById('imagesUploadText');
    const uploadLabel = document.getElementById('imagesUploadLabel');
    const clearAllBtn = document.getElementById('imagesClearAll');

    // Existing image count (used to enforce 10 total: existing + new)
    const existingImageCount = {{ $product->images->count() }};

    // Store selected files
    let selectedFiles = [];

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    // Create preview item HTML
    function createPreviewItem(file, index) {
        const objectUrl = URL.createObjectURL(file);
        const item = document.createElement('div');
        item.className = 'admin-preview-item is-new';
        item.dataset.index = index;
        item.innerHTML = `
            <img src="${objectUrl}" alt="${file.name}">
            <div class="admin-preview-item-info">
                <span class="admin-preview-item-name" title="${file.name}">${file.name}</span>
                <span class="admin-preview-item-size">${formatFileSize(file.size)}</span>
            </div>
            <button type="button" class="admin-preview-item-remove" data-index="${index}" title="Xóa ảnh này">×</button>
        `;
        return item;
    }

    // Update preview section
    function updatePreview() {
        previewGrid.innerHTML = '';

        if (selectedFiles.length === 0) {
            previewSection.hidden = true;
            uploadText.textContent = 'Chọn hình ảnh hoặc kéo thả vào đây';
            uploadLabel.style.borderColor = '';
            uploadLabel.style.background = '';
            return;
        }

        previewSection.hidden = false;
        previewCount.textContent = selectedFiles.length;
        uploadText.textContent = `Đã chọn ${selectedFiles.length} ảnh (click để thêm)`;
        uploadLabel.style.borderColor = '#22c55e';
        uploadLabel.style.background = '#f0fdf4';

        selectedFiles.forEach((file, index) => {
            const item = createPreviewItem(file, index);
            previewGrid.appendChild(item);
        });
    }

    // Handle file selection
    function handleFileSelect(files) {
        const newFiles = Array.from(files);
        // Limit to 10 total including existing
        const remaining = 10 - existingImageCount - selectedFiles.length;
        if (newFiles.length > remaining) {
            alert(`Mỗi sản phẩm chỉ có tối đa 10 ảnh. Hiện có ${existingImageCount} ảnh cũ, bạn chỉ có thể thêm tối đa ${remaining} ảnh mới.`);
            newFiles.splice(remaining);
        }
        if (newFiles.length === 0) return;
        selectedFiles = selectedFiles.concat(newFiles);

        // Update the file input with all selected files
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;

        updatePreview();
    }

    // Handle remove single file
    function handleRemoveFile(index) {
        // Revoke object URL to free memory
        const item = previewGrid.querySelector(`[data-index="${index}"]`);
        if (item) {
            const img = item.querySelector('img');
            if (img && img.src.startsWith('blob:')) {
                URL.revokeObjectURL(img.src);
            }
        }
        selectedFiles.splice(index, 1);

        // Update file input
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;

        updatePreview();
    }

    // File input change
    if (fileInput) {
        fileInput.addEventListener('change', function (e) {
            handleFileSelect(e.target.files);
        });
    }

    // Click on upload label
    if (uploadLabel) {
        uploadLabel.addEventListener('click', function (e) {
            // Don't preventDefault on label - it has for="images" which naturally opens file picker
            e.stopPropagation();
        });
    }

    // Clear all button
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            previewGrid.querySelectorAll('img').forEach(img => {
                if (img.src.startsWith('blob:')) {
                    URL.revokeObjectURL(img.src);
                }
            });
            selectedFiles = [];
            fileInput.value = '';
            updatePreview();
        });
    }

    // Remove button click (event delegation)
    if (previewGrid) {
        previewGrid.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.admin-preview-item-remove');
            if (removeBtn) {
                e.preventDefault();
                e.stopPropagation();
                const index = parseInt(removeBtn.dataset.index, 10);
                handleRemoveFile(index);
            }
        });
    }

    // Drag and drop
    if (uploadLabel) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadLabel.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadLabel.addEventListener(eventName, () => {
                uploadLabel.style.borderColor = '#22c55e';
                uploadLabel.style.background = '#dcfce7';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadLabel.addEventListener(eventName, () => {
                if (selectedFiles.length === 0) {
                    uploadLabel.style.borderColor = '';
                    uploadLabel.style.background = '';
                }
            }, false);
        });

        uploadLabel.addEventListener('drop', function (e) {
        const dt = e.dataTransfer;
        if (dt && dt.files) {
            handleFileSelect(dt.files);
        }
    }, false);
    }
    } catch (err) {
        console.error('[Image Preview] Initialization failed:', err);
    }
});

// ============================================
// URL IMAGE PREVIEW
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    try {
    const urlInput = document.getElementById('image_url');
    const urlPreview = document.getElementById('imageUrlPreview');
    const urlPreviewImg = document.getElementById('imageUrlPreviewImg');
    const urlPreviewRemove = document.getElementById('imageUrlPreviewRemove');

    if (!urlInput) return;

    urlInput.addEventListener('input', function () {
        const url = urlInput.value.trim();

        if (!url) {
            urlPreview.hidden = true;
            return;
        }

        try {
            new URL(url);
        } catch {
            urlPreview.hidden = true;
            return;
        }

        urlPreviewImg.src = url;
        urlPreview.hidden = false;
    });

    if (urlPreviewRemove) {
        urlPreviewRemove.addEventListener('click', function () {
            urlInput.value = '';
            urlPreview.hidden = true;
            urlPreviewImg.src = '';
            urlInput.focus();
        });
    }

    urlPreviewImg.addEventListener('error', function () {
        urlPreview.hidden = true;
    });
    } catch (err) {
        console.error('[URL Preview] Initialization failed:', err);
    }
});
</script>
