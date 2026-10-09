@extends('layouts.store')

@section('title', $product['name'])
@section('description', $product['description'])
@section('content')
    <section class="page-intro detail-intro">
        <div class="container">
            <span class="eyebrow">{{ $product['category'] }} · CỐC MƠ STUDIO</span>
            <p class="detail-intro-copy">Một thiết kế nhỏ,<br><strong>mang dấu ấn của bạn.</strong></p>
            <div class="breadcrumb"><a href="{{ route('home') }}">Trang chủ</a><span>/</span><a href="{{ route('products.index') }}">Sản phẩm</a><span>/</span><span>{{ $product['name'] }}</span></div>
        </div>
    </section>
    <section class="container detail-section reveal">
        <div class="detail-grid">
            <div class="detail-image"><img src="{{ $product['image'] }}" alt="{{ $product['name'] }}"></div>
            <div class="detail-copy">
                <span class="eyebrow">{{ $product['category'] }} · THIẾT KẾ THEO YÊU CẦU</span>
                <h1>{{ $product['name'] }}</h1>
                <p class="detail-price">{{ number_format($product['price'], 0, ',', '.') }}đ</p>
                <p class="detail-description">{{ $product['description'] }}</p>
                <a class="button button-dark detail-cta" href="{{ route('contact') }}">Thiết kế chiếc cốc này <span aria-hidden="true">↗</span></a>
                <p class="detail-delivery"><span aria-hidden="true">✳</span> Gói quà xinh xắn · Hỗ trợ giao hàng toàn quốc</p>
                <div class="detail-specs">
                    <h2>Thông tin sản phẩm</h2>
                    <ul>
                        @foreach ($product['details'] as $detail)
                            <li>{{ $detail }}</li>
                        @endforeach
                    </ul>
                </div>
                <a class="back-link" href="{{ route('products.index') }}">← Quay lại bộ sưu tập</a>
            </div>
        </div>
    </section>
    <section class="container related-section reveal">
        <div class="section-heading"><div><span class="eyebrow">CÓ THỂ BẠN CŨNG THÍCH</span><h2>Thêm chút <em>dịu dàng.</em></h2></div></div>
        <div class="product-grid">
            @foreach ($relatedProducts as $relatedProduct)
                @include('components.product-card', ['product' => $relatedProduct])
            @endforeach
        </div>
    </section>
@endsection
