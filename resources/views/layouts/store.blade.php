<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('description', config('store.name').' — những chiếc cốc xinh được làm riêng cho bạn.')">
    <title>@yield('title', config('store.name')) — Cốc xinh theo cách của bạn</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="cup-storefront cup-original @yield('body-class')">
    <div class="announcement">Gửi chút xinh xắn đến bạn — miễn phí gói quà cho đơn từ 300.000đ</div>
    <header class="site-header">
        <div class="container nav-wrap">
            <a class="brand" href="{{ route('home') }}" aria-label="Cốc Mơ - Trang chủ">
                <span class="brand-mark" aria-hidden="true">✳</span>
                <span>{{ mb_strtoupper(config('store.name')) }}<small>CỐC XINH · IN THEO YÊU CẦU</small></span>
            </a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="main-navigation" aria-label="Mở menu">
                <span></span><span></span>
            </button>
            <nav class="main-nav" id="main-navigation" aria-label="Điều hướng chính">
                <a @class(['active' => request()->routeIs('home')]) href="{{ route('home') }}">Trang chủ</a>
                <a @class(['active' => request()->routeIs('products.*')]) href="{{ route('products.index') }}">Sản phẩm</a>
                <a @class(['active' => request()->routeIs('about')]) href="{{ route('about') }}">Giới thiệu</a>
                <a @class(['active' => request()->routeIs('contact')]) href="{{ route('contact') }}">Liên hệ</a>
            </nav>
            <a
                class="nav-cta"
                href="{{ request()->routeIs('home') ? route('products.index') : route('contact') }}"
                @if (request()->routeIs('home')) aria-label="Xem tất cả sản phẩm" @endif
            ><span class="nav-cta-label">Tạo cốc của bạn</span><span aria-hidden="true">♥</span></a>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-main">
            <div class="footer-brand">
                <a class="brand brand-light" href="{{ route('home') }}">
                    <span class="brand-mark" aria-hidden="true">✳</span>
                    <span>{{ mb_strtoupper(config('store.name')) }}<small>CỐC XINH · IN THEO YÊU CẦU</small></span>
                </a>
                <p>Thêm ảnh, thêm tên, thêm một chút yêu thương vào chiếc cốc của riêng bạn.</p>
            </div>
            <div class="footer-column">
                <h2>Khám phá</h2>
                <a href="{{ route('products.index') }}">Tất cả sản phẩm</a>
                <a href="{{ route('about') }}">Câu chuyện {{ config('store.name') }}</a>
                <a href="{{ route('contact') }}">Ghé thăm chúng tôi</a>
            </div>
            <div class="footer-column">
                <h2>Kết nối</h2>
                <a href="mailto:{{ config('store.email') }}">{{ config('store.email') }}</a>
                <a href="tel:{{ preg_replace('/\s+/', '', config('store.phone')) }}">{{ config('store.phone') }}</a>
                <span>{{ config('store.address') }}</span>
            </div>
        </div>
        <div class="container footer-bottom">
            <span>© {{ date('Y') }} {{ config('store.name') }}. Một chiếc cốc, một niềm vui.</span>
            <a href="{{ route('contact') }}">Cần chúng mình giúp đỡ? <span aria-hidden="true">↗</span></a>
        </div>
    </footer>
</body>
</html>
