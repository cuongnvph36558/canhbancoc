@extends('layouts.store')

@section('title', 'Thiết kế cốc của bạn')
@section('description', 'Chọn mẫu cốc, thêm chữ, icon và ảnh riêng — kéo thả trực tiếp lên cốc rồi đặt hàng.')
@section('body-class', 'cup-customizer-page')
@section('content')
    @php
        $initialDesignItems = old('design.items', [
            ['type' => 'text', 'content' => 'Tên của bạn', 'x' => 11, 'y' => 39, 'width' => 78],
            ['type' => 'icon', 'content' => '☕', 'x' => 40, 'y' => 63, 'width' => 20],
        ]);
        $firstVariantSlug = array_key_first($customCup['variants'] ?? []) ?: 'black';
        $selectedVariant = old('cup_variant', $firstVariantSlug);
    @endphp

    <section class="cup-designer-page">
        <div class="container cup-designer-container">
            <div class="cup-designer-heading">
                <h1><span aria-hidden="true">☕</span> Thiết kế cốc của bạn</h1>
                <p>Chọn mẫu cốc, thêm chữ, hình, ảnh riêng – kéo thả trực tiếp lên cốc rồi đặt hàng.</p>
            </div>

            @if ($errors->any())
                <div class="cup-designer-errors" role="alert">
                    <strong>Đơn hàng chưa được gửi.</strong>
                    <span>Vui lòng kiểm tra lại thông tin nhận hàng và thiết kế của bạn.</span>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                class="cup-designer-layout"
                method="POST"
                action="{{ route('custom-cup-orders.store') }}"
                enctype="multipart/form-data"
                data-cup-customizer
                data-initial-items="{{ json_encode($initialDesignItems, JSON_HEX_APOS | JSON_HEX_QUOT) }}"
                data-unit-price="{{ $customCup['unit_price'] }}"
                data-discount-minimum="{{ $customCup['bulk_discount_minimum'] }}"
                data-discount-percentage="{{ $customCup['bulk_discount_percentage'] }}"
                data-new-text="Chữ của bạn"
            >
                @csrf

                <section class="cup-designer-preview-card" aria-label="Bản xem trước thiết kế">
                    <div class="cup-designer-preview-stage" data-cup-preview data-cup-variant="black">
                        <svg class="cup-designer-mug" viewBox="0 0 800 650" role="img" aria-label="Bản xem trước chiếc cốc sứ">
                            <defs>
                                <linearGradient id="mug-body-shade" x1="0" x2="1" y1="0" y2=".3">
                                    <stop offset="0" stop-color="#d1d1d1"></stop>
                                    <stop offset=".16" stop-color="#fbfbfb"></stop>
                                    <stop offset=".78" stop-color="#fff"></stop>
                                    <stop offset="1" stop-color="#d2d2d2"></stop>
                                </linearGradient>
                                <linearGradient id="mug-handle-shade" x1="0" x2="1">
                                    <stop offset="0" stop-color="#d1d1d1"></stop>
                                    <stop offset=".42" stop-color="var(--cup-handle-color)"></stop>
                                    <stop offset="1" stop-color="#c7c7c7"></stop>
                                </linearGradient>
                            </defs>
                            <ellipse class="cup-mug-shadow" cx="415" cy="596" rx="240" ry="28"></ellipse>
                            <path class="cup-mug-handle" d="M607 251h47c91 0 100 216 0 216h-56v-47h52c45 0 42-122 0-122h-43z"></path>
                            <path class="cup-mug-body" d="M223 155h398l-37 378c-4 43-29 68-72 68H334c-43 0-68-25-72-68z"></path>
                            <ellipse class="cup-mug-rim" cx="422" cy="156" rx="199" ry="31"></ellipse>
                            <ellipse class="cup-mug-interior" cx="422" cy="156" rx="185" ry="22"></ellipse>
                            <path class="cup-mug-highlight" d="M272 198h19l35 340h-17z"></path>
                        </svg>
                        <div class="cup-print-area" data-print-area aria-label="Vùng in. Kéo chữ, biểu tượng hoặc hình ảnh vào đây.">
                            <div class="cup-print-area-outline" aria-hidden="true"></div>
                            <div class="cup-design-items" data-design-items></div>
                            <span class="cup-print-placeholder" data-print-placeholder>Thả thiết kế của bạn vào đây</span>
                            <span class="cup-selected-layer-hint" data-layer-hint hidden>Chạm vào thiết kế để chọn, kéo để di chuyển</span>
                        </div>
                    </div>
                    <p class="cup-designer-caption">Chạm hoặc kéo để di chuyển · Vùng nét đứt là vùng in</p>
                </section>

                <div class="cup-designer-controls">
                    <section class="cup-editor-section" aria-labelledby="cup-style-heading">
                        <h2 id="cup-style-heading"><span>1.</span> Chọn mẫu cốc</h2>
                        <div class="cup-style-options">
                            @foreach ($customCup['variants'] as $variant => $details)
                                <button
                                    class="cup-style-option{{ $selectedVariant === $variant ? ' is-selected' : '' }}"
                                    type="button"
                                    data-cup-variant-option="{{ $variant }}"
                                    aria-pressed="{{ $selectedVariant === $variant ? 'true' : 'false' }}"
                                >
                                    <span class="cup-miniature" data-cup-variant="{{ $variant }}" aria-hidden="true">
                                        <span class="cup-miniature-handle"></span>
                                        <span class="cup-miniature-body"><span class="cup-miniature-rim"></span></span>
                                    </span>
                                    <span class="cup-style-name">{{ $details['name'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </section>

                    <section class="cup-editor-section" aria-labelledby="cup-content-heading">
                        <h2 id="cup-content-heading"><span>2.</span> Thêm nội dung</h2>
                        <div class="cup-content-actions">
                            <button class="cup-editor-secondary-button" type="button" data-add-text>＋ Thêm chữ</button>
                            <label class="cup-editor-secondary-button cup-upload-button" for="cup-artwork">▧ Tải ảnh lên</label>
                            <input
                                class="cup-artwork-input"
                                id="cup-artwork"
                                name="artwork"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                data-artwork-input
                            >
                        </div>
                        <p class="cup-upload-caption" data-upload-caption>JPG, PNG hoặc WEBP · Tối đa 5 MB</p>
                        <div class="cup-background-removal-controls">
                            <button class="cup-editor-secondary-button" type="button" data-remove-background disabled>
                                Tách nền tự động
                            </button>
                            <button class="cup-editor-secondary-button" type="button" data-restore-background hidden>
                                Khôi phục ảnh gốc
                            </button>
                            <p class="cup-background-removal-status" data-background-removal-status role="status" aria-live="polite"></p>
                            <p class="cup-background-removal-note">Sau khi chọn ảnh, trình duyệt sẽ tải trước mô hình AI (lần đầu khoảng 71 MB, các lần sau dùng bộ nhớ đệm) để tách nền nhanh hơn. Ảnh được xử lý ngay trên thiết bị, không gửi lên dịch vụ xử lý ảnh.</p>
                        </div>
                        <div class="cup-icon-options" aria-label="Chọn biểu tượng">
                            @foreach ($customCup['icons'] as $icon)
                                <button
                                    class="cup-icon-option"
                                    type="button"
                                    data-add-icon="{{ $icon }}"
                                    aria-label="Thêm biểu tượng {{ $icon }} vào cốc"
                                    draggable="true"
                                >{{ $icon }}</button>
                            @endforeach
                        </div>
                        <div class="cup-layer-editor" data-layer-editor hidden>
                            <label for="cup-selected-content">Nội dung đã chọn</label>
                            <input id="cup-selected-content" type="text" maxlength="80" data-selected-content>
                            <label class="cup-font-size-control" data-font-size-control for="cup-selected-font-size" hidden>
                                Cỡ chữ
                                <output data-font-size-value>100%</output>
                                <input id="cup-selected-font-size" type="range" min="50" max="200" step="5" value="100" data-selected-font-size>
                            </label>
                            <button class="cup-remove-layer" type="button" data-remove-layer>Xóa mục đã chọn</button>
                        </div>
                        <p class="cup-drag-help">Chạm vào chữ để sửa nội dung và cỡ chữ; kéo thiết kế để di chuyển hoặc chọn góc để đổi cỡ ảnh, biểu tượng.</p>
                    </section>

                    <section class="cup-editor-section" aria-labelledby="cup-shipping-heading">
                        <h2 id="cup-shipping-heading"><span>3.</span> Thông tin nhận hàng</h2>
                        <div class="cup-customer-fields">
                            <label for="cup-customer-name">Họ và tên
                                <input id="cup-customer-name" type="text" name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" autocomplete="name" required maxlength="120" placeholder="Tên người nhận">
                            </label>
                            <label for="cup-customer-phone">Số điện thoại
                                <input id="cup-customer-phone" type="tel" name="customer_phone" value="{{ old('customer_phone', auth()->user()?->phone) }}" autocomplete="tel" required minlength="8" maxlength="30" placeholder="09xx xxx xxx">
                            </label>
                            <label for="cup-customer-email">Email <span>(không bắt buộc)</span>
                                <input id="cup-customer-email" type="email" name="customer_email" value="{{ old('customer_email', auth()->user()?->email) }}" autocomplete="email" maxlength="255" placeholder="ban@email.com">
                            </label>
                            <div class="cup-shipping-address" data-vietnam-address>
                                <label for="cup-shipping-province">Tỉnh/thành phố
                                    <select id="cup-shipping-province" name="shipping_province" required data-vietnam-province
                                        data-selected-province="{{ old('shipping_province', auth()->user()?->shipping_province) }}">
                                        <option value="">Đang tải danh sách tỉnh/thành...</option>
                                    </select>
                                    @error('shipping_province') <span class="form-error">{{ $message }}</span> @enderror
                                </label>
                                <label for="cup-shipping-ward">Phường/xã
                                    <select id="cup-shipping-ward" name="shipping_ward" required data-vietnam-ward
                                        data-selected-ward="{{ old('shipping_ward', auth()->user()?->shipping_ward) }}" disabled>
                                        <option value="">Chọn tỉnh/thành phố trước</option>
                                    </select>
                                    @error('shipping_ward') <span class="form-error">{{ $message }}</span> @enderror
                                </label>
                                <p class="form-error" data-address-error role="status" hidden></p>
                            </div>
                            <label class="cup-shipping-address" for="cup-shipping-address">Địa chỉ nhận hàng
                                <textarea id="cup-shipping-address" name="shipping_address" required maxlength="500" autocomplete="street-address" placeholder="Số nhà, tên đường...">{{ old('shipping_address', auth()->user()?->shipping_address) }}</textarea>
                            </label>
                        </div>
                    </section>

                    <section class="cup-editor-section cup-order-section" aria-labelledby="cup-order-heading">
                        <h2 id="cup-order-heading"><span>4.</span> Đặt hàng</h2>
                        <div class="cup-order-options">
                            <label for="cup-size">Loại cốc
                                <select id="cup-size" name="cup_size_display" disabled>
                                    <option value="{{ $customCup['size'] }}">Cốc sứ {{ $customCup['size'] }} – {{ number_format($customCup['unit_price'], 0, ',', '.') }}đ</option>
                                </select>
                            </label>
                            <label for="cup-quantity">Số lượng
                                <input
                                    id="cup-quantity"
                                    type="number"
                                    name="quantity"
                                    value="{{ old('quantity', 1) }}"
                                    min="1"
                                    max="99"
                                    required
                                    data-cup-quantity
                                >
                            </label>
                        </div>
                        <input type="hidden" name="cup_variant" value="{{ old('cup_variant', 'black') }}" data-selected-variant>
                        <input type="hidden" name="design_preview" value="" data-design-preview>
                        <div class="cup-order-total">
                            <div>
                                <span data-cup-discount-note>Từ {{ $customCup['bulk_discount_minimum'] }} cốc giảm {{ $customCup['bulk_discount_percentage'] }}%</span>
                                <strong data-cup-order-total aria-live="polite">{{ number_format($customCup['unit_price'], 0, ',', '.') }}đ</strong>
                                <small data-cup-order-unit>{{ number_format($customCup['unit_price'], 0, ',', '.') }}đ / cốc</small>
                            </div>
                            <button class="cup-order-submit" type="submit">Đặt hàng</button>
                        </div>
                        <p class="cup-order-note">Chưa thanh toán trực tuyến. Cốc Mơ sẽ liên hệ bạn để xác nhận đơn hàng.</p>
                    </section>
                </div>

                <div class="cup-design-hidden-fields" data-design-fields></div>
            </form>
        </div>
    </section>
@endsection
