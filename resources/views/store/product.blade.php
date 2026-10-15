@extends('layouts.store')

@section('title', $product['name'])
@section('description', $product['description'] ?: 'Khám phá '.$product['name'].' tại Cốc Mơ. Tư vấn thiết kế riêng, gói quà xinh và giao hàng toàn quốc.')
@section('content')
    @php
        $productImages = ($product['images'] ?? []) ?: array_values(array_filter([$product['image'] ?? null]));
        $isAvailable = ($product['stock'] ?? 1) > 0;
        $productDescription = $product['description'] ?: 'Mỗi chiếc cốc có thể được cá nhân hóa theo ý tưởng riêng. Liên hệ với Cốc Mơ để trao đổi về hình ảnh, tên hoặc lời nhắn bạn muốn in.';
        $orderUrl = route('contact', [
            'subject' => 'Đặt sản phẩm: '.$product['name'],
            'message' => 'Mình muốn đặt '.$product['name'].'. Vui lòng tư vấn thêm về mẫu này.',
        ]);
    @endphp

    <section class="product-page">
        <div class="container">
            <nav class="product-breadcrumb" aria-label="Điều hướng">
                <a href="{{ route('home') }}">Trang chủ</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('products.index') }}">Sản phẩm</a>
                @if (! empty($product['category_slug']))
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('products.category', $product['category_slug']) }}">{{ $product['category'] }}</a>
                @endif
                <span aria-hidden="true">/</span>
                <span aria-current="page">{{ $product['name'] }}</span>
            </nav>

            <div class="product-purchase-layout">
                <div class="product-gallery" data-product-gallery>
                    <div class="product-gallery-stage">
                        @if (! empty($productImages))
                            <img
                                class="product-gallery-main-image"
                                data-product-main-image
                                src="{{ $productImages[0] }}"
                                alt="{{ $product['name'] }}"
                                fetchpriority="high"
                            >
                            @if (count($productImages) > 1)
                                <span class="product-gallery-counter" data-product-image-counter>1 / {{ count($productImages) }}</span>
                            @endif
                            @if (($product['discount_percentage'] ?? 0) > 0)
                                <span class="product-gallery-sale">-{{ $product['discount_percentage'] }}%</span>
                            @endif
                        @else
                            <div class="product-gallery-placeholder" role="img" aria-label="Sản phẩm chưa có hình ảnh">
                                <span aria-hidden="true">☕</span>
                                <span>Hình ảnh sản phẩm đang được cập nhật</span>
                            </div>
                        @endif
                    </div>

                    @if (count($productImages) > 1)
                        <div class="product-gallery-thumbnails" role="group" aria-label="Chọn hình ảnh sản phẩm">
                            @foreach ($productImages as $imageIndex => $image)
                                <button
                                    class="product-gallery-thumbnail{{ $imageIndex === 0 ? ' is-selected' : '' }}"
                                    type="button"
                                    data-product-thumbnail
                                    data-product-image="{{ $image }}"
                                    data-product-alt="{{ $product['name'] }} — ảnh {{ $imageIndex + 1 }}"
                                    aria-label="Xem ảnh {{ $imageIndex + 1 }} của {{ $product['name'] }}"
                                    aria-pressed="{{ $imageIndex === 0 ? 'true' : 'false' }}"
                                >
                                    <img src="{{ $image }}" alt="" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                    <p class="product-gallery-caption"><span aria-hidden="true">✳</span> Mỗi thiết kế được chăm chút dành riêng cho bạn</p>
                </div>

                <section class="product-buy-card" aria-labelledby="product-title">
                    <div class="product-buy-kicker">
                        <span>{{ $product['category'] }}</span>
                        @if (! empty($product['badge']))
                            <span class="product-made-to-order">{{ $product['badge'] }}</span>
                        @else
                            <span class="product-made-to-order"><span aria-hidden="true">✦</span> Thiết kế theo yêu cầu</span>
                        @endif
                    </div>
                    <h1 id="product-title">{{ $product['name'] }}</h1>
                    @if ($reviewCount > 0)
                        <a class="product-review-overview" href="#product-reviews">
                            <span class="product-review-stars" aria-label="{{ number_format((float) $reviewAverage, 1) }} trên 5 sao">{{ str_repeat('★', (int) round((float) $reviewAverage)) }}{{ str_repeat('☆', 5 - (int) round((float) $reviewAverage)) }}</span>
                            <strong>{{ number_format((float) $reviewAverage, 1) }}/5</strong>
                            <span>{{ $reviewCount }} đánh giá</span>
                        </a>
                    @endif

                    <div class="product-price-block">
                        @if (($product['discount_percentage'] ?? 0) > 0)
                            <div class="product-current-price">{{ number_format($product['discounted_price'], 0, ',', '.') }}<span>đ</span></div>
                            <div class="product-price-note">
                                <s>{{ number_format($product['price'], 0, ',', '.') }}đ</s>
                                <span class="product-detail-discount">Giảm {{ $product['discount_percentage'] }}%</span>
                            </div>
                            <p class="product-savings">Bạn tiết kiệm {{ number_format($product['price'] - $product['discounted_price'], 0, ',', '.') }}đ</p>
                        @else
                            <div class="product-current-price">{{ number_format($product['price'], 0, ',', '.') }}<span>đ</span></div>
                            <p class="product-price-note">{{ ! empty($product['database_product_id']) ? 'Giá bán · Thanh toán trực tuyến an toàn' : 'Giá mẫu tham khảo · Chốt chi tiết trước khi đặt' }}</p>
                        @endif
                    </div>

                    <p class="product-buy-description">{{ $productDescription }}</p>

                    @if (! empty($product['colors']))
                        <div class="product-colors">
                            <label for="color-select" class="product-colors-label">Màu sắc:</label>
                            <select id="color-select" name="color_id" class="product-color-select">
                                @foreach ($product['colors'] as $index => $color)
                                    <option value="{{ $color['id'] ?? $index }}" data-hex="{{ $color['has_hex'] ? $color['hex_code'] : '' }}" data-name="{{ $color['name'] }}" data-has-hex="{{ $color['has_hex'] ? '1' : '0' }}" {{ $index === 0 ? 'selected' : '' }}>
                                        {{ $color['name'] }}{{ $color['has_hex'] ? '' : ' (theo tên)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="product-stock-status{{ $isAvailable ? ' is-available' : ' is-unavailable' }}" role="status">
                        <span class="product-stock-dot" aria-hidden="true"></span>
                        {{ $isAvailable ? (! empty($product['database_product_id']) ? 'Sẵn sàng giao hàng — còn '.$product['stock'].' sản phẩm' : 'Sẵn sàng nhận yêu cầu đặt hàng') : 'Tạm hết hàng — liên hệ để được tư vấn' }}
                    </div>

                    <div class="product-buy-actions">
                        @if (! empty($product['database_product_id']) && $isAvailable)
                            <form class="product-cart-form" method="POST" action="{{ route('cart.store') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product['database_product_id'] }}">
                                @php
                                    $initialColor = $product['colors'][0] ?? null;
                                    $initialColorName = $initialColor['name'] ?? '';
                                    $initialColorHex = $initialColor['hex_code'] ?? '';
                                @endphp
                                <input type="hidden" name="custom_color" id="custom-color-name" value="{{ $initialColorName }}">
                                <input type="hidden" name="custom_color_hex" id="custom-color-hex" value="{{ $initialColorHex }}">
                                
                                <div class="product-custom-text">
                                    <div class="custom-text-group">
                                        <label for="custom-name" class="custom-label">
                                            <span class="custom-label-icon">✏️</span>
                                            Tên/Text in trên cốc
                                        </label>
                                        <input 
                                            type="text" 
                                            id="custom-name" 
                                            name="custom_text" 
                                            class="custom-input" 
                                            placeholder="VD: Yêu em, Happy Birthday, ..."
                                            maxlength="100"
                                        >
                                        <small class="custom-hint">Tối đa 100 ký tự</small>
                                    </div>
                                    
                                    <div class="custom-text-group">
                                        <label for="custom-image" class="custom-label">
                                            <span class="custom-label-icon">🖼️</span>
                                            Hình ảnh muốn in (nếu có)
                                        </label>
                                        <div class="custom-file-upload">
                                            <input 
                                                type="file" 
                                                id="custom-image" 
                                                name="custom_image" 
                                                class="custom-file-input" 
                                                accept="image/*"
                                            >
                                            <label for="custom-image" class="custom-file-label">
                                                <span class="file-upload-icon">📁</span>
                                                <span class="file-upload-text">Chọn hình ảnh</span>
                                            </label>
                                            <span class="file-name" id="file-name"></span>
                                        </div>
                                        <small class="custom-hint">Chấp nhận: JPG, PNG, GIF (tối đa 5MB)</small>

                                        {{-- Preview ảnh đã chọn (cho khách thấy ngay ảnh gì) --}}
                                        <div class="product-image-preview" id="product-image-preview" hidden>
                                            <div class="product-image-preview-header">
                                                <span class="product-image-preview-title">Ảnh đã chọn:</span>
                                                <button type="button" class="product-image-preview-remove" id="product-image-preview-remove" aria-label="Xóa ảnh đã chọn">×</button>
                                            </div>
                                            <div class="product-image-preview-thumb">
                                                <img id="product-image-preview-img" src="" alt="Preview ảnh đã chọn">
                                            </div>
                                            <div class="product-image-preview-meta">
                                                <span class="product-image-preview-name" id="product-image-preview-name"></span>
                                                <span class="product-image-preview-size" id="product-image-preview-size"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <label for="product-quantity">Số lượng</label>
                                <input id="product-quantity" type="number" name="quantity" value="1" min="1" max="{{ $product['stock'] }}" required>
                                <button id="product-cart-submit" class="product-order-button" type="submit">
                                    <span>Thêm vào giỏ hàng</span>
                                    <span aria-hidden="true">＋</span>
                                </button>
                                @error('quantity')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </form>
                            <a class="product-customize-button" href="{{ $orderUrl }}">Cần tư vấn về sản phẩm này? <span aria-hidden="true">↗</span></a>
                        @else
                            <a class="product-order-button" href="{{ $orderUrl }}">
                                <span>Đặt hàng / Nhờ tư vấn</span>
                                <span aria-hidden="true">↗</span>
                            </a>
                        @endif
                        <a class="product-customize-button" href="{{ route('products.customize') }}">
                            <span aria-hidden="true">✳</span> Tự thiết kế chiếc cốc của bạn
                        </a>
                    </div>

                    <div class="product-reassurance" aria-label="Quyền lợi khi đặt hàng">
                        <div>
                            <span class="product-reassurance-icon" aria-hidden="true">✿</span>
                            <span><strong>Gói quà miễn phí</strong><small>Cho đơn từ 300.000đ</small></span>
                        </div>
                        <div>
                            <span class="product-reassurance-icon" aria-hidden="true">♡</span>
                            <span><strong>Tư vấn tận tình</strong><small>Chốt mẫu trước khi làm</small></span>
                        </div>
                        <div>
                            <span class="product-reassurance-icon" aria-hidden="true">⌖</span>
                            <span><strong>Giao hàng toàn quốc</strong><small>Gói hàng cẩn thận</small></span>
                        </div>
                    </div>
                    <p class="product-contact-note">
                        Cần hỏi nhanh?
                        <a href="tel:{{ preg_replace('/\s+/', '', config('store.phone')) }}">Gọi {{ config('store.phone') }}</a>
                    </p>
                </section>
            </div>

            <section class="product-reviews" id="product-reviews" aria-labelledby="product-reviews-title">
                <div class="section-heading">
                    <div>
                        <span class="eyebrow">CHIA SẺ TỪ KHÁCH HÀNG</span>
                        <h2 id="product-reviews-title">Đánh giá <em>sản phẩm.</em></h2>
                    </div>
                    @if ($reviewCount > 0)
                        <p class="product-review-summary">
                            <strong>{{ number_format((float) $reviewAverage, 1) }}/5</strong>
                            <span>từ {{ $reviewCount }} đánh giá</span>
                        </p>
                    @endif
                </div>
                <div class="product-review-list">
                    @forelse ($productReviews as $review)
                        <article class="product-review-card">
                            <div class="product-review-card-heading">
                                <strong>{{ $review->user->name }}</strong>
                                <span class="product-review-stars" aria-label="{{ $review->rating }} trên 5 sao">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                <time datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('d/m/Y') }}</time>
                            </div>
                            <span class="product-review-verified">Đã mua và nhận hàng</span>
                            @if ($review->comment)
                                <p>{{ $review->comment }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="product-reviews-empty">Sản phẩm chưa có đánh giá. Bạn có thể chia sẻ cảm nhận sau khi nhận hàng.</p>
                    @endforelse
                </div>
            </section>

            <section class="product-information" id="product-information" aria-labelledby="product-information-title">
                <div class="product-description-card">
                    <span class="product-section-eyebrow">MỘT MÓN QUÀ MANG DẤU ẤN RIÊNG</span>
                    <h2 id="product-information-title">Về sản phẩm</h2>
                    <p>{{ $productDescription }}</p>

                    @if (! empty($product['details']))
                        <ul class="product-detail-list">
                            @foreach ($product['details'] as $detail)
                                <li><span aria-hidden="true">✓</span>{{ $detail }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="product-order-steps">
                    <span class="product-section-eyebrow">THẬT DỄ ĐỂ BẮT ĐẦU</span>
                    <h2>Đặt hàng trong 3 bước</h2>
                    <ol>
                        <li><span>01</span><div><strong>Gửi yêu cầu</strong><small>Cho chúng mình biết mẫu bạn thích.</small></div></li>
                        <li><span>02</span><div><strong>Cùng hoàn thiện</strong><small>Trao đổi hình ảnh, tên hoặc lời nhắn.</small></div></li>
                        <li><span>03</span><div><strong>Xác nhận đơn</strong><small>Cốc Mơ xác nhận chi tiết cùng bạn.</small></div></li>
                    </ol>
                    <a href="{{ $orderUrl }}">Bắt đầu đặt hàng <span aria-hidden="true">↗</span></a>
                </div>
            </section>
        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="container related-section reveal">
            <div class="section-heading">
                <div><span class="eyebrow">CÓ THỂ BẠN CŨNG THÍCH</span><h2>Thêm chút <em>dịu dàng.</em></h2></div>
                <a class="cup-view-all" href="{{ route('products.index') }}">Xem tất cả sản phẩm <span aria-hidden="true">→</span></a>
            </div>
            <div class="product-grid">
                @foreach ($relatedProducts as $relatedProduct)
                    @include('components.product-card', ['product' => $relatedProduct])
                @endforeach
            </div>
        </section>
    @endif

    <div class="product-mobile-order-bar">
        <div>
            <span>Giá bán</span>
            <strong>{{ number_format(($product['discount_percentage'] ?? 0) > 0 ? $product['discounted_price'] : $product['price'], 0, ',', '.') }}đ</strong>
        </div>
        @if (! empty($product['database_product_id']) && $isAvailable)
            <form method="POST" action="{{ route('cart.store') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product['database_product_id'] }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit">Thêm vào giỏ <span aria-hidden="true">＋</span></button>
            </form>
        @else
            <a href="{{ $orderUrl }}">Đặt hàng <span aria-hidden="true">↗</span></a>
        @endif
    </div>
@endsection

@push('styles')
<style>
/* === Product image preview (hiển thị ảnh đã chọn cho khách thấy ngay) === */
.product-image-preview {
    display: flex !important;
    flex-direction: column !important;
    gap: 8px !important;
    margin-top: 10px !important;
    padding: 10px !important;
    background: linear-gradient(135deg, #fdf2f8 0%, #fff 100%) !important;
    border: 2px dashed #f0abfc !important;
    border-radius: 10px !important;
    box-sizing: border-box !important;
    width: 100% !important;
}
.product-image-preview[hidden] {
    display: none !important;
}
.product-image-preview-header {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 8px !important;
    width: 100% !important;
}
.product-image-preview-title {
    font-weight: 600 !important;
    color: #831843 !important;
    font-size: 13px !important;
}
.product-image-preview-remove {
    width: 24px !important;
    height: 24px !important;
    border-radius: 50% !important;
    background: rgba(239, 68, 68, 0.95) !important;
    color: #fff !important;
    border: 2px solid #fff !important;
    font-size: 16px !important;
    line-height: 1 !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15) !important;
    transition: all 0.2s ease !important;
}
.product-image-preview-remove:hover {
    background: #dc2626 !important;
    transform: scale(1.1) !important;
}
.product-image-preview-thumb {
    width: 100% !important;
    max-width: 140px !important;
    aspect-ratio: 4 / 3 !important;
    border-radius: 8px !important;
    overflow: hidden !important;
    border: 2px solid #fce7f3 !important;
    background: #f9fafb !important;
    margin: 0 auto !important;
    box-sizing: border-box !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.product-image-preview-thumb img {
    display: block !important;
    max-width: 100% !important;
    max-height: 100% !important;
    width: auto !important;
    height: auto !important;
    object-fit: contain !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
}
.product-image-preview-meta {
    display: flex !important;
    flex-direction: column !important;
    gap: 2px !important;
    width: 100% !important;
}
.product-image-preview-name {
    font-size: 12px !important;
    font-weight: 600 !important;
    color: #1f2937 !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    display: block !important;
}
.product-image-preview-size {
    font-size: 11px !important;
    color: #9ca3af !important;
    display: block !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('custom-image');
    const fileNameDisplay = document.getElementById('file-name');
    const colorSelect = document.getElementById('color-select');
    const colorNameInput = document.getElementById('custom-color-name');
    const colorHexInput = document.getElementById('custom-color-hex');

    // Image preview elements
    const previewBox = document.getElementById('product-image-preview');
    const previewImg = document.getElementById('product-image-preview-img');
    const previewName = document.getElementById('product-image-preview-name');
    const previewSize = document.getElementById('product-image-preview-size');
    const previewRemove = document.getElementById('product-image-preview-remove');

    function showImagePreview(file) {
        if (!file || !file.type.startsWith('image/')) {
            hideImagePreview();
            return;
        }
        const url = URL.createObjectURL(file);
        previewImg.src = url;
        previewImg.onload = function() { URL.revokeObjectURL(url); };
        previewName.textContent = file.name;
        previewName.title = file.name;
        const sizeKb = Math.round(file.size / 1024);
        previewSize.textContent = sizeKb > 1024 ? (sizeKb/1024).toFixed(1) + ' MB' : sizeKb + ' KB';
        previewBox.hidden = false;
    }
    function hideImagePreview() {
        previewBox.hidden = true;
        previewImg.src = '';
        previewName.textContent = '';
        previewSize.textContent = '';
        if (fileInput) fileInput.value = '';
        if (fileNameDisplay) fileNameDisplay.textContent = '';
    }

    if (fileInput && fileNameDisplay) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                fileNameDisplay.textContent = '📎 ' + file.name;
                showImagePreview(file);
            } else {
                fileNameDisplay.textContent = '';
                hideImagePreview();
            }
        });
    }

    if (previewRemove) {
        previewRemove.addEventListener('click', hideImagePreview);
    }

    if (colorSelect && colorNameInput && colorHexInput) {
        // Update hidden fields when color changes
        function updateColorFields() {
            const selectedOption = colorSelect.options[colorSelect.selectedIndex];
            colorNameInput.value = selectedOption.dataset.name || '';
            colorHexInput.value = selectedOption.dataset.hex || '';
            console.log('[color] set', { name: colorNameInput.value, hex: colorHexInput.value, selectedIndex: colorSelect.selectedIndex });
        }
        colorSelect.addEventListener('change', updateColorFields);
        updateColorFields(); // Set initial values

        // Re-sync right before submit to avoid stale values if user changed selection
        const cartForm = document.querySelector('.product-cart-form');
        if (cartForm) {
            cartForm.addEventListener('submit', updateColorFields);
        }
    }
});
</script>
@endpush
