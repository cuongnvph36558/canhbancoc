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

                    <div class="product-stock-status{{ $isAvailable ? ' is-available' : ' is-unavailable' }}" role="status">
                        <span class="product-stock-dot" aria-hidden="true"></span>
                        {{ $isAvailable ? (! empty($product['database_product_id']) ? 'Sẵn sàng giao hàng — còn '.$product['stock'].' sản phẩm' : 'Sẵn sàng nhận yêu cầu đặt hàng') : 'Tạm hết hàng — liên hệ để được tư vấn' }}
                    </div>

                    <div class="product-buy-actions">
                        @if (! empty($product['database_product_id']) && $isAvailable)
                            <form class="product-cart-form" method="POST" action="{{ route('cart.store') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product['database_product_id'] }}">
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
