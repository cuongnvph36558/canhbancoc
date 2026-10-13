@extends('layouts.store')

@section('title', 'Thiết kế cốc của bạn')
@section('description', 'Chọn mẫu cốc, thêm chữ, hình, ảnh riêng — kéo thả trực tiếp lên cốc rồi đặt hàng.')
@section('body-class', 'cup-customizer-page cup-customizer-canvas')
@section('content')
    @php
        $initialDesignItems = old('design.items', [
            ['type' => 'text', 'content' => 'Tên của bạn', 'x' => 11, 'y' => 39, 'width' => 78, 'font_size' => 100],
            ['type' => 'icon', 'content' => '☕', 'x' => 40, 'y' => 63, 'width' => 20, 'font_size' => 100],
        ]);
        $firstVariantSlug = array_key_first($customCup['variants'] ?? []) ?: 'black';
        $selectedVariant = old('cup_variant', $firstVariantSlug);
    @endphp

    <section class="cup-designer-page">
        <header class="cup-designer-header">
            <h1><span aria-hidden="true">☕</span> Thiết kế cốc của bạn</h1>
            <p>Chọn mẫu cốc, thêm chữ, hình, ảnh riêng — kéo thả trực tiếp lên cốc rồi đặt hàng.</p>
        </header>

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

        <div class="cup-designer-layout"
            data-cup-customizer
            data-initial-items="{{ json_encode($initialDesignItems, JSON_HEX_APOS | JSON_HEX_QUOT) }}"
            data-unit-price="{{ $customCup['unit_price'] }}"
            data-discount-minimum="{{ $customCup['bulk_discount_minimum'] }}"
            data-discount-percentage="{{ $customCup['bulk_discount_percentage'] }}"
            data-new-text="Chữ của bạn"
            data-mug-sprite-base="{{ asset('img') }}"
        >
            <section class="cup-designer-preview-card" aria-label="Bản xem trước thiết kế">
                <div class="cup-designer-preview-stage" data-cup-preview>
                    <div class="cup-designer-mug">
                        <img data-cup-mug-sprite alt="Mẫu cốc" draggable="false">
                    </div>
                    <canvas class="cup-print-canvas" data-cup-canvas aria-label="Vùng in. Kéo chữ, biểu tượng hoặc hình ảnh vào đây."></canvas>
                </div>
                <p class="cup-designer-caption">Chạm hoặc kéo để di chuyển · Vùng nét đứt là vùng in</p>
            </section>

            <section class="cup-designer-controls" aria-label="Tùy chọn thiết kế">
                <div class="cup-designer-section" data-section="mug">
                    <h2><span>1</span> Chọn mẫu cốc</h2>
                    <div class="cup-style-options" data-cup-style-options>
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
                </div>

                <div class="cup-designer-section" data-section="content">
                    <h2><span>2</span> Thêm nội dung</h2>
                    <div class="cup-content-actions">
                        <button class="cup-editor-secondary-button" type="button" data-add-text>＋ Thêm chữ</button>
                        <label class="cup-editor-secondary-button cup-upload-button" for="cup-artwork">▧ Thêm ảnh</label>
                        <input
                            class="cup-artwork-input"
                            id="cup-artwork"
                            name="artwork[]"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            data-artwork-input
                            multiple
                        >
                    </div>
                    <p class="cup-upload-caption" data-upload-caption>JPG, PNG hoặc WEBP · Tối đa 5 MB</p>

                    <div class="cup-icon-options" aria-label="Chọn biểu tượng">
                        @foreach ($customCup['icons'] as $icon)
                            <button
                                class="cup-icon-option"
                                type="button"
                                data-add-icon="{{ $icon }}"
                                aria-label="Thêm biểu tượng {{ $icon }} vào cốc"
                            >{{ $icon }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="cup-designer-section" data-section="layer" data-layer-section hidden>
                    <h2><span>3</span> Phần đang chọn</h2>
                    <div class="cup-layer-editor" data-layer-editor>
                        <div data-layer-text-only hidden>
                            <label for="cup-selected-content">Nội dung chữ</label>
                            <input id="cup-selected-content" type="text" maxlength="80" data-selected-content>
                            <label for="cup-selected-font">Phông chữ</label>
                            <select id="cup-selected-font" data-selected-font>
                                <option value='"Be Vietnam Pro",sans-serif'>Be Vietnam Pro</option>
                                <option value='"Pacifico",cursive'>Pacifico</option>
                                <option value='"Oswald",sans-serif'>Oswald</option>
                                <option value='"Dancing Script",cursive'>Dancing Script</option>
                            </select>
                        </div>
                        <div data-layer-image-only hidden>
                            <button class="cup-editor-secondary-button" type="button" data-remove-background>✨ Tách nền tự động</button>
                            <div class="cup-bg-tolerance" data-bg-tolerance-wrap hidden>
                                <label for="cup-bg-tolerance">Độ nhạy tách nền: <span data-bg-tolerance-value>40</span></label>
                                <input id="cup-bg-tolerance" type="range" min="8" max="120" value="40" data-bg-tolerance>
                                <p class="cup-bg-tolerance-note">Hiệu quả nhất với ảnh nền trơn. Kéo thanh trượt nếu còn sót nền.</p>
                            </div>
                            <p class="cup-bg-status" data-bg-status role="status" aria-live="polite"></p>
                        </div>
                        <div class="cup-layer-color-row" data-layer-color-row hidden>
                            <label for="cup-selected-color">Màu</label>
                            <input id="cup-selected-color" type="color" value="#111111" data-selected-color>
                        </div>
                        <label for="cup-selected-size">Kích thước</label>
                        <input id="cup-selected-size" type="range" min="16" max="220" value="100" data-selected-size>
                        <label for="cup-selected-rotation">Xoay</label>
                        <input id="cup-selected-rotation" type="range" min="-180" max="180" value="0" data-selected-rotation>
                        <div class="cup-layer-actions">
                            <button class="cup-layer-action" type="button" data-front-layer>Đưa lên trên</button>
                            <button class="cup-layer-action is-danger" type="button" data-remove-layer>Xóa</button>
                        </div>
                    </div>

                    <div class="cup-image-layers" data-image-layers hidden>
                        <p class="cup-image-layers-title">Các ảnh đang dùng</p>
                        <div class="cup-image-layers-list" data-image-layers-list></div>
                    </div>
                </div>

                <div class="cup-designer-section cup-designer-order" data-section="order">
                    <h2><span>4</span> Đặt hàng</h2>
                    <div class="cup-order-options">
                        <div>
                            <label for="cup-type">Loại cốc</label>
                            <select id="cup-type" data-cup-type>
                                <option value="89000" data-size="11oz" selected>Cốc sứ 11oz — 89.000đ</option>
                            </select>
                        </div>
                        <div>
                            <label for="cup-qty">Số lượng</label>
                            <input id="cup-qty" type="text" inputmode="numeric" value="1" data-cup-quantity>
                        </div>
                    </div>
                    <div class="cup-order-summary">
                        <div>
                            <p data-cup-discount-note>Từ {{ $customCup['bulk_discount_minimum'] }} cốc giảm {{ $customCup['bulk_discount_percentage'] }}%</p>
                            <strong data-cup-order-total>{{ number_format($customCup['unit_price'], 0, ',', '.') }}đ</strong>
                        </div>
                    </div>
                    <div class="cup-order-actions">
                        <button class="cup-buy-now" type="button" data-open-order>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            Mua ngay
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <div class="cup-customizer-help">
            <p><strong>💡 Mẹo:</strong> Bấm vào ảnh, chữ, biểu tượng trên cốc để chỉnh; kéo để di chuyển. Thử tách nền ảnh để chỉ giữ lại phần bạn muốn in.</p>
        </div>
    </section>

    {{-- Order confirmation modal --}}
    <div class="cup-modal" data-cup-modal hidden>
        <div class="cup-modal-backdrop" data-close-modal></div>
        <div class="cup-modal-card" role="dialog" aria-modal="true" aria-labelledby="cup-modal-title">
            <button type="button" class="cup-modal-close" aria-label="Đóng" data-close-modal>×</button>
            <h2 id="cup-modal-title">Xác nhận đơn hàng</h2>
            <img data-modal-preview alt="Bản xem trước thiết kế">
            <p data-modal-summary class="cup-modal-summary"></p>
            <form method="POST" action="{{ route('custom-cup-orders.store') }}" enctype="multipart/form-data" data-order-form>
                @csrf
                <div data-design-fields></div>
                <input type="hidden" name="cup_variant" data-selected-variant>
                <input type="hidden" name="quantity" data-quantity>
                <input type="hidden" name="design_preview" value="" data-design-preview>
                <label>Họ và tên<input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" autocomplete="name" required maxlength="120" placeholder="Tên người nhận"></label>
                <label>Số điện thoại<input type="tel" name="customer_phone" value="{{ old('customer_phone', auth()->user()?->phone) }}" autocomplete="tel" required minlength="8" maxlength="30" placeholder="09xx xxx xxx"></label>
                <label>Email (không bắt buộc)<input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()?->email) }}" autocomplete="email" maxlength="255" placeholder="ban@email.com"></label>
                <div data-vietnam-address>
                    <label>Tỉnh/thành phố
                        <select name="shipping_province" required data-vietnam-province data-selected-province="{{ old('shipping_province', auth()->user()?->shipping_province) }}">
                            <option value="">Đang tải danh sách tỉnh/thành...</option>
                        </select>
                    </label>
                    <label>Phường/xã
                        <select name="shipping_ward" required data-vietnam-ward data-selected-ward="{{ old('shipping_ward', auth()->user()?->shipping_ward) }}" disabled>
                            <option value="">Chọn tỉnh/thành phố trước</option>
                        </select>
                    </label>
                    <p data-address-error role="status" hidden></p>
                </div>
                <label>Địa chỉ nhận hàng<textarea name="shipping_address" required maxlength="500" autocomplete="street-address" placeholder="Số nhà, tên đường...">{{ old('shipping_address', auth()->user()?->shipping_address) }}</textarea></label>
                <p class="cup-modal-note">Chưa thanh toán trực tuyến. Cốc Mơ sẽ liên hệ bạn để xác nhận đơn hàng.</p>
                <div class="cup-modal-actions">
                    <button type="button" class="cup-modal-secondary" data-close-modal>Đóng</button>
                    <button type="submit" class="cup-modal-primary">Gửi đơn</button>
                </div>
            </form>
        </div>
    </div>

    <div class="cup-toast" data-cup-toast role="status" aria-live="polite" hidden></div>
@endsection
