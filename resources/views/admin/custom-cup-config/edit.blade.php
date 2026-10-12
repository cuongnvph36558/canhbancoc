@extends('layouts.admin')

@section('title', 'Cấu hình cốc custom')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">THIẾT KẾ THEO YÊU CẦU</span>
            <h1>Cấu hình cốc custom</h1>
            <p>Chỉnh giá bán, chương trình giảm giá theo số lượng, các mẫu cốc và biểu tượng hiển thị trên trang customizer.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success" role="status">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="admin-alert admin-alert-error" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="admin-products-panel" aria-labelledby="settings-heading">
        <div class="admin-panel-heading">
            <div>
                <h2 id="settings-heading">Giá bán & chương trình giảm giá</h2>
                <p>Thay đổi giá sẽ áp dụng cho các đơn cốc custom mới ngay sau khi lưu.</p>
            </div>
        </div>

        <form class="admin-config-form" method="POST" action="{{ route('admin.custom-cup-config.update-settings') }}">
            @csrf
            @method('PATCH')

            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label for="unit-price">Đơn giá (₫) <span class="admin-required">*</span></label>
                    <input type="number" id="unit-price" name="unit_price" value="{{ old('unit_price', $settings->unit_price) }}" min="0" step="1000" required>
                    <small>Giá hiển thị trên trang customizer và dùng để tính tổng đơn hàng.</small>
                </div>
                <div class="admin-form-group">
                    <label for="size">Dung tích / kích thước <span class="admin-required">*</span></label>
                    <input type="text" id="size" name="size" value="{{ old('size', $settings->size) }}" maxlength="20" required placeholder="VD: 11oz">
                    <small>Hiển thị cùng tên sản phẩm cho khách.</small>
                </div>
            </div>

            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label for="bulk-discount-minimum">Số lượng tối thiểu để được giảm</label>
                    <input type="number" id="bulk-discount-minimum" name="bulk_discount_minimum" value="{{ old('bulk_discount_minimum', $settings->bulk_discount_minimum) }}" min="1" max="99" required>
                    <small>Khách phải đặt ít nhất số cốc này mới được hưởng giảm giá.</small>
                </div>
                <div class="admin-form-group">
                    <label for="bulk-discount-percentage">Phần trăm giảm giá (%)</label>
                    <input type="number" id="bulk-discount-percentage" name="bulk_discount_percentage" value="{{ old('bulk_discount_percentage', $settings->bulk_discount_percentage) }}" min="0" max="100" required>
                    <small>Nhập 0 nếu muốn tắt chương trình giảm giá theo số lượng.</small>
                </div>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn-primary">Lưu thiết lập</button>
            </div>
        </form>
    </section>

    <section class="admin-products-panel" aria-labelledby="variants-heading">
        <div class="admin-panel-heading">
            <div>
                <h2 id="variants-heading">Phân loại mẫu cốc</h2>
                <p>Mỗi phân loại tương ứng với một biến thể mà khách có thể chọn ở bước 1.</p>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Tên hiển thị</th>
                        <th>Màu quai</th>
                        <th>Màu lòng</th>
                        <th>Trạng thái</th>
                        <th class="admin-actions-heading">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($variants as $variant)
                        <tr>
                            <td><code>{{ $variant->slug }}</code></td>
                            <td>
                                <form class="admin-config-inline" method="POST" action="{{ route('admin.custom-cup-config.variants.update', $variant) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="name" value="{{ $variant->name }}" maxlength="120" required class="admin-config-inline-name">
                            </td>
                            <td>
                                    <input type="color" name="handle_color" value="{{ $variant->handle_color }}" required class="admin-color-input">
                            </td>
                            <td>
                                    <input type="color" name="interior_color" value="{{ $variant->interior_color }}" required class="admin-color-input">
                            </td>
                            <td>
                                    <label class="admin-config-inline-toggle">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" @checked($variant->is_active)>
                                        <span>{{ $variant->is_active ? 'Đang hiển thị' : 'Đang ẩn' }}</span>
                                    </label>
                            </td>
                            <td>
                                    <div class="admin-row-actions">
                                        <button type="submit" class="admin-action-button">Cập nhật</button>
                                </form>
                                <form method="POST" action="{{ route('admin.custom-cup-config.variants.destroy', $variant) }}" onsubmit="return confirm('Xóa phân loại {{ $variant->name }}?');" class="admin-inline-form">
                                    @csrf
                                    @method('DELETE')
                                        <button type="submit" class="admin-action-button is-danger">Xóa</button>
                                    </form>
                                    </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-empty-state">Chưa có phân loại mẫu cốc nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <form class="admin-config-add-form" method="POST" action="{{ route('admin.custom-cup-config.variants.store') }}">
            @csrf
            <h3>Thêm phân loại mới</h3>
            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label for="variant-name">Tên hiển thị <span class="admin-required">*</span></label>
                    <input type="text" id="variant-name" name="name" value="{{ old('name') }}" maxlength="120" required placeholder="VD: Quai xanh – lòng xanh">
                </div>
                <div class="admin-form-group">
                    <label for="variant-handle-color">Màu quai <span class="admin-required">*</span></label>
                    <input type="color" id="variant-handle-color" name="handle_color" value="{{ old('handle_color', '#242322') }}" required class="admin-color-input">
                </div>
                <div class="admin-form-group">
                    <label for="variant-interior-color">Màu lòng <span class="admin-required">*</span></label>
                    <input type="color" id="variant-interior-color" name="interior_color" value="{{ old('interior_color', '#171717') }}" required class="admin-color-input">
                </div>
            </div>
            <div class="admin-form-actions">
                <button type="submit" class="admin-btn-primary">Thêm phân loại</button>
            </div>
        </form>
    </section>

    <section class="admin-products-panel" aria-labelledby="icons-heading">
        <div class="admin-panel-heading">
            <div>
                <h2 id="icons-heading">Biểu tượng trang trí</h2>
                <p>Khách có thể chạm vào biểu tượng để thêm nhanh vào thiết kế.</p>
            </div>
        </div>

        <div class="admin-icon-grid">
            @forelse ($icons as $icon)
                <div class="admin-icon-cell">
                    <span class="admin-icon-glyph" aria-hidden="true">{{ $icon->glyph }}</span>
                    <form method="POST" action="{{ route('admin.custom-cup-config.icons.destroy', $icon) }}" onsubmit="return confirm('Xóa biểu tượng {{ $icon->glyph }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-action-button is-danger">Xóa</button>
                    </form>
                </div>
            @empty
                <p class="admin-empty-state">Chưa có biểu tượng nào.</p>
            @endforelse
        </div>

        <form class="admin-config-add-form" method="POST" action="{{ route('admin.custom-cup-config.icons.store') }}">
            @csrf
            <h3>Thêm biểu tượng mới</h3>
            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label for="icon-glyph">Ký tự biểu tượng <span class="admin-required">*</span></label>
                    <input type="text" id="icon-glyph" name="glyph" value="{{ old('glyph') }}" maxlength="16" required placeholder="VD: 💗 hoặc ❤">
                    <small>Có thể dùng emoji hoặc ký tự Unicode bất kỳ (tối đa 16 ký tự).</small>
                </div>
            </div>
            <div class="admin-form-actions">
                <button type="submit" class="admin-btn-primary">Thêm biểu tượng</button>
            </div>
        </form>
    </section>

    <section class="admin-products-panel" aria-labelledby="preview-heading">
        <div class="admin-panel-heading">
            <div>
                <h2 id="preview-heading">Xem trước dữ liệu khách đang thấy</h2>
                <p>Dữ liệu này được cache và sẽ tự làm mới sau khi bạn lưu thay đổi ở trên.</p>
            </div>
        </div>
        <pre class="admin-config-preview">{{ json_encode($previewConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </section>
@endsection

<style>
.admin-config-form,
.admin-config-add-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 22px;
}

.admin-config-add-form {
    padding-top: 0;
}

.admin-config-add-form h3 {
    margin: 8px 0 0;
    color: var(--admin-ink);
    font-family: var(--sans);
    font-size: 13px;
    font-weight: 750;
}

.admin-config-add-form:first-of-type h3 {
    margin-top: 0;
}

.admin-form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.admin-form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.admin-form-group label {
    color: var(--admin-ink);
    font-size: 11px;
    font-weight: 600;
}

.admin-required {
    color: #d32f2f;
}

.admin-form-group input,
.admin-form-group textarea,
.admin-config-inline input[type="text"],
.admin-config-inline input[type="color"] {
    min-height: 38px;
    padding: 0 12px;
    border: 1px solid #dce8f0;
    border-radius: 8px;
    background: #fff;
    color: var(--admin-ink);
    font: inherit;
    font-size: 12px;
    transition: border-color .2s, box-shadow .2s;
}

.admin-form-group input:focus,
.admin-form-group textarea:focus,
.admin-config-inline input:focus {
    outline: none;
    border-color: #438fc3;
    box-shadow: 0 0 0 3px rgba(67, 143, 195, .15);
}

.admin-form-group small {
    color: #8a9dad;
    font-size: 11px;
}

.admin-form-actions {
    display: flex;
    gap: 12px;
}

.admin-btn-primary {
    min-height: 38px;
    padding: 0 18px;
    border: 1px solid #438fc3;
    border-radius: 8px;
    background: #438fc3;
    color: #fff;
    cursor: pointer;
    font: inherit;
    font-size: 11px;
    font-weight: 750;
    transition: background .2s;
}

.admin-btn-primary:hover {
    background: #2f78ab;
}

.admin-color-input {
    width: 56px;
    height: 38px;
    padding: 2px;
    cursor: pointer;
}

.admin-config-inline {
    display: contents;
}

.admin-config-inline-name {
    min-width: 140px;
}

.admin-config-inline-toggle {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: var(--admin-ink);
}

.admin-config-inline-toggle input[type="checkbox"] {
    width: 16px;
    height: 16px;
}

.admin-icon-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    padding: 22px;
}

.admin-icon-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    min-width: 78px;
    padding: 12px 14px;
    border: 1px solid #edf2f6;
    border-radius: 12px;
    background: #f9fbfd;
}

.admin-icon-glyph {
    font-size: 26px;
    line-height: 1;
}

.admin-icon-cell form {
    margin: 0;
}

.admin-config-preview {
    margin: 0 22px 22px;
    padding: 16px;
    border-radius: 10px;
    background: #0f2236;
    color: #e7f1fa;
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', monospace;
    font-size: 11px;
    line-height: 1.6;
    overflow-x: auto;
}

.admin-empty-state {
    padding: 22px;
    text-align: center;
    color: #8a9dad;
    font-size: 12px;
}
</style>