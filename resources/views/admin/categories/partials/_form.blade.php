{{-- Shared form fields for creating & editing categories. --}}
@php
    $presetIcons = ['☕', '✨', '♡', '✿', '🎁', '☀', '🌷', '🍵', '🍓', '✦', '🌿', '🐱', '🍶', '🌸', '🧊', '📔'];
    $selectedIcon = old('icon', $category->icon ?? null);
    $isEdit = ($category ?? null) !== null;
@endphp

@if ($errors->any())
    <div class="admin-alert admin-alert-error" role="alert">
        <strong>Không thể lưu phân loại:</strong>
        <ul style="margin: 6px 0 0; padding-left: 18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="admin-form" data-category-form>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    {{-- Section 1: Thông tin cơ bản --}}
    <div class="admin-form-section">
        <div>
            <h3 class="admin-form-section-title">Thông tin cơ bản</h3>
            <p class="admin-form-section-subtitle">Tên và đường dẫn hiển thị trên trang khách hàng.</p>
        </div>

        <div class="admin-form-row">
            <div class="admin-form-group">
                <div class="label-row">
                    <label for="name">Tên phân loại <span class="admin-required">*</span></label>
                    <small>Tối đa 120 ký tự</small>
                </div>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $category->name ?? '') }}"
                    required
                    maxlength="120"
                    placeholder="VD: Cốc sứ, Cốc giữ nhiệt, Phụ kiện..."
                    autocomplete="off"
                    autofocus
                    data-sync-slug="slug"
                >
                <small class="admin-form-help">Đây là tên khách hàng thấy ở menu và trang sản phẩm.</small>
            </div>

            <div class="admin-form-group">
                <div class="label-row">
                    <label for="slug">Đường dẫn (slug)</label>
                    <span class="admin-char-count" data-char-count-for="slug">—</span>
                </div>
                <div class="admin-input-with-prefix">
                    <span class="admin-input-prefix">/danh-muc/</span>
                    <input
                        type="text"
                        id="slug"
                        name="slug"
                        value="{{ old('slug', $category->slug ?? '') }}"
                        maxlength="140"
                        placeholder="tu-dong-tao-tu-ten"
                        autocomplete="off"
                        data-slug-from="name"
                    >
                </div>
                <small class="admin-form-help">Để trống để tự động tạo từ tên. Chỉ gồm chữ thường, số và dấu gạch ngang.</small>
            </div>
        </div>
    </div>

    {{-- Section 2: Hiển thị --}}
    <div class="admin-form-section">
        <div>
            <h3 class="admin-form-section-title">Hiển thị trên cửa hàng</h3>
            <p class="admin-form-section-subtitle">Tinh chỉnh biểu tượng, thứ tự và mô tả ngắn cho phân loại.</p>
        </div>

        <div class="admin-form-row">
            <div class="admin-form-group">
                <div class="label-row">
                    <label for="icon">Biểu tượng</label>
                    <small>Hiển thị cạnh tên phân loại</small>
                </div>
                <div class="admin-icon-picker" data-icon-picker>
                    <div class="admin-icon-input-row">
                        <div class="admin-icon-preview" data-icon-preview aria-live="polite">{{ $selectedIcon ?: '🗂' }}</div>
                        <input
                            type="text"
                            id="icon"
                            name="icon"
                            value="{{ $selectedIcon }}"
                            maxlength="8"
                            placeholder="Chọn hoặc dán emoji"
                            data-icon-input
                        >
                    </div>
                    <div class="admin-icon-presets" role="radiogroup" aria-label="Chọn biểu tượng nhanh">
                        @foreach ($presetIcons as $preset)
                            <button
                                type="button"
                                class="admin-icon-preset @selected($selectedIcon === $preset)"
                                data-icon-preset="{{ $preset }}"
                                role="radio"
                                aria-checked="{{ $selectedIcon === $preset ? 'true' : 'false' }}"
                                aria-label="Chọn biểu tượng {{ $preset }}"
                            >{{ $preset }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="admin-form-group">
                <div class="label-row">
                    <label for="position">Thứ tự hiển thị</label>
                    <small>Số nhỏ sẽ hiện trước</small>
                </div>
                <input
                    type="number"
                    id="position"
                    name="position"
                    value="{{ old('position', $category->position ?? 0) }}"
                    min="0"
                    max="1000"
                    step="1"
                >
                <small class="admin-form-help">Dùng để sắp xếp thứ tự xuất hiện trên menu và trang chủ.</small>
            </div>
        </div>

        <div class="admin-form-group">
            <div class="label-row">
                <label for="description">Mô tả</label>
                <span class="admin-char-count" data-char-count-for="description" data-max="500">0/500</span>
            </div>
            <textarea
                id="description"
                name="description"
                rows="3"
                maxlength="500"
                placeholder="Mô tả ngắn gọn về nhóm sản phẩm này. Ví dụ: Cốc sứ cao cấp, in hình theo yêu cầu."
            >{{ old('description', $category->description ?? '') }}</textarea>
            <small class="admin-form-help">Hiển thị dưới tiêu đề phân loại ở trang khách hàng.</small>
        </div>
    </div>

    {{-- Section 3: Trạng thái + Preview --}}
    <div class="admin-form-section">
        <div>
            <h3 class="admin-form-section-title">Trạng thái &amp; xem trước</h3>
            <p class="admin-form-section-subtitle">Bật/tắt hiển thị và xem nhanh phân loại sẽ trông như thế nào.</p>
        </div>

        <div class="admin-form-row">
            <div class="admin-form-group">
                <label class="admin-switch {{ old('is_active', $category->is_active ?? true) ? 'is-on' : '' }}" data-switch>
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', $category->is_active ?? true))
                        data-switch-input
                    >
                    <span class="admin-switch-track" aria-hidden="true"></span>
                    <span class="admin-switch-text">
                        <strong data-switch-label>{{ old('is_active', $category->is_active ?? true) ? 'Đang hiển thị' : 'Đang ẩn' }}</strong>
                        <small>Bật để phân loại xuất hiện trên menu, trang chủ và trang khách hàng.</small>
                    </span>
                </label>
            </div>

            <div class="admin-form-group">
                <div class="admin-preview-card" data-preview-card>
                    <span class="admin-preview-label">Xem trước ở cửa hàng</span>
                    <span class="admin-preview-chip" data-preview-chip>
                        <span data-preview-icon>{{ $selectedIcon ?: '🗂' }}</span>
                        <span data-preview-name>{{ old('name', $category->name ?? 'Tên phân loại') }}</span>
                        <small>0</small>
                    </span>
                    <p class="admin-preview-meta" data-preview-description>
                        {{ old('description', $category->description ?? 'Mô tả phân loại sẽ hiển thị ở đây.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="admin-form-actions">
        <div class="admin-form-actions-left">
            <a href="{{ route('admin.categories.index') }}" class="admin-btn-link">← Quay lại danh sách</a>
        </div>
        <div class="admin-form-actions-right">
            <a href="{{ route('admin.categories.index') }}" class="admin-btn-secondary">Hủy</a>
            <button type="submit" class="admin-btn-primary">
                <span>{{ $isEdit ? 'Lưu thay đổi' : 'Tạo phân loại' }}</span>
                <span aria-hidden="true">→</span>
            </button>
        </div>
    </div>
</form>

@once
    @push('scripts')
        <script>
            (function () {
                const form = document.querySelector('[data-category-form]');

                if (! form) {
                    return;
                }

                const nameInput = form.querySelector('[data-sync-slug="slug"]');
                const slugInput = form.querySelector('[data-slug-from]');
                const iconInput = form.querySelector('[data-icon-input]');
                const iconPreview = form.querySelector('[data-icon-preview]');
                const presetButtons = form.querySelectorAll('[data-icon-preset]');
                const descriptionInput = form.querySelector('#description');
                const previewName = form.querySelector('[data-preview-name]');
                const previewIcon = form.querySelector('[data-preview-icon]');
                const previewDescription = form.querySelector('[data-preview-description]');
                const charCounters = form.querySelectorAll('[data-char-count-for]');
                const switchInput = form.querySelector('[data-switch-input]');
                const switchLabel = form.querySelector('[data-switch-label]');
                const switchWrapper = form.querySelector('[data-switch]');

                const slugify = (value) => {
                    if (typeof value !== 'string') {
                        return '';
                    }

                    return value
                        .toString()
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .toLowerCase()
                        .replace(/đ/g, 'd')
                        .trim()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .replace(/^-+|-+$/g, '');
                };

                if (nameInput && slugInput) {
                    nameInput.addEventListener('input', () => {
                        if (slugInput.dataset.dirty === 'true') {
                            return;
                        }
                        slugInput.value = slugify(nameInput.value);
                        slugInput.dispatchEvent(new Event('input'));
                    });

                    slugInput.addEventListener('input', () => {
                        slugInput.dataset.dirty = slugInput.value.length > 0 ? 'true' : 'false';
                    });
                }

                const updatePreview = () => {
                    if (previewName && nameInput) {
                        previewName.textContent = nameInput.value.trim() || 'Tên phân loại';
                    }

                    if (previewDescription && descriptionInput) {
                        previewDescription.textContent = descriptionInput.value.trim()
                            || 'Mô tả phân loại sẽ hiển thị ở đây.';
                    }

                    if (previewIcon && iconInput) {
                        const iconValue = iconInput.value.trim();
                        previewIcon.textContent = iconValue || '🗂';
                    }
                };

                if (iconInput) {
                    iconInput.addEventListener('input', () => {
                        if (iconPreview) {
                            iconPreview.textContent = iconInput.value.trim() || '🗂';
                        }
                        updatePreview();

                        presetButtons.forEach((button) => {
                            const isSelected = button.dataset.iconPreset === iconInput.value;
                            button.classList.toggle('is-selected', isSelected);
                            button.setAttribute('aria-checked', isSelected ? 'true' : 'false');
                        });
                    });
                }

                presetButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        if (! iconInput) {
                            return;
                        }
                        iconInput.value = button.dataset.iconPreset;
                        iconInput.dispatchEvent(new Event('input'));
                        iconInput.focus();
                    });
                });

                if (nameInput) {
                    nameInput.addEventListener('input', updatePreview);
                }

                if (descriptionInput) {
                    descriptionInput.addEventListener('input', updatePreview);
                }

                charCounters.forEach((counter) => {
                    const targetId = counter.getAttribute('data-char-count-for');
                    const max = Number(counter.getAttribute('data-max') || 0);
                    const target = document.getElementById(targetId);

                    if (! target) {
                        counter.textContent = max ? `0/${max}` : '—';
                        return;
                    }

                    const update = () => {
                        const length = target.value.length;
                        if (max) {
                            counter.textContent = `${length}/${max}`;
                            counter.classList.toggle('is-warning', length >= max * 0.8 && length < max);
                            counter.classList.toggle('is-danger', length >= max);
                        } else {
                            counter.textContent = length.toString();
                        }
                    };

                    target.addEventListener('input', update);
                    update();
                });

                if (switchInput && switchLabel && switchWrapper) {
                    const updateSwitch = () => {
                        const isOn = switchInput.checked;
                        switchWrapper.classList.toggle('is-on', isOn);
                        switchLabel.textContent = isOn ? 'Đang hiển thị' : 'Đang ẩn';
                    };

                    switchInput.addEventListener('change', updateSwitch);
                    updateSwitch();
                }
            })();
        </script>
    @endpush
@endonce
