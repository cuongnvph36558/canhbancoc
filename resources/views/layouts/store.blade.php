@php
    $siteHeaderFooter = app(\App\Services\SitePageContent::class)->get('header_footer');
    $headerBrandName = $siteHeaderFooter['brand_name'];
    $headerLogoUrl = $siteHeaderFooter['logo_image_url'] ?: (asset('img/logo.png') . '?v=' . filemtime(public_path('img/logo.png')));
@endphp

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('description', config('store.name').' — những chiếc cốc xinh được làm riêng cho bạn.')">
    <title>@yield('title', config('store.name')) — Cốc xinh theo cách của bạn</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="cup-storefront cup-original @yield('body-class')"
    data-cart-count="{{ array_sum(session('cart', [])) }}"
    data-realtime-role="{{ auth()->user()?->isAdmin() ? 'admin' : (auth()->check() ? 'client' : 'guest') }}"
    data-realtime-user-id="{{ auth()->id() ?? '' }}"
>
    <div class="announcement">{{ $siteHeaderFooter['announcement_text'] }}</div>
    <header class="site-header">
        <div class="container nav-wrap">
            <a class="brand" href="{{ route('home') }}" aria-label="{{ $headerBrandName }} - Trang chủ">
                <img src="{{ $headerLogoUrl }}" alt="{{ $headerBrandName }}" class="brand-logo">
            </a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="main-navigation" aria-label="Mở menu">
                <span></span><span></span>
            </button>
            <nav class="main-nav" id="main-navigation" aria-label="Điều hướng chính">
                <a @class(['active' => request()->routeIs('home')]) href="{{ route('home') }}">{{ $siteHeaderFooter['nav_home_label'] }}</a>
                <a @class(['active' => request()->routeIs('products.index', 'products.show')]) href="{{ route('products.index') }}">{{ $siteHeaderFooter['nav_products_label'] }}</a>
                <a @class(['active' => request()->routeIs('products.customize')]) href="{{ route('products.customize') }}">{{ $siteHeaderFooter['nav_custom_label'] }}</a>
                <a @class(['active' => request()->routeIs('about')]) href="{{ route('about') }}">{{ $siteHeaderFooter['nav_about_label'] }}</a>
                <a @class(['active' => request()->routeIs('contact')]) href="{{ route('contact') }}">{{ $siteHeaderFooter['nav_contact_label'] }}</a>
                @auth
                    <div class="user-menu">
                        <span class="user-greeting">Xin chào, {{ auth()->user()->name }}</span>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="nav-auth-link">Quản lý</a>
                        @endif
                        <a href="{{ route('customer.orders.index') }}">Theo dõi đơn hàng</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="logout-button">Đăng xuất</button>
                        </form>
                    </div>
                @else
                    <div class="auth-links">
                        <a href="{{ route('login') }}" class="nav-auth-link">{{ $siteHeaderFooter['nav_login_label'] }}</a>
                        <a href="{{ route('register') }}" class="nav-cta"><span class="nav-cta-label">{{ $siteHeaderFooter['nav_register_label'] }}</span><span aria-hidden="true">♥</span></a>
                    </div>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <div class="storefront-quick-actions">
        <div class="storefront-live-chat">
            <a href="{{ auth()->check() ? route('chat.index') : route('login') }}" class="storefront-live-chat__button" aria-label="Tin nhắn">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5H6l-3 2v-5.5A7.5 7.5 0 1 1 20 11.5Z"></path>
                    <path d="M8 11h8M8 14h5"></path>
                </svg>
            </a>
        </div>
        <div class="storefront-live-cart" aria-live="polite" aria-label="Giỏ hàng">
            <a href="{{ route('cart.index') }}" class="storefront-live-cart__button" aria-label="Mở giỏ hàng">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 4h2l2.3 11.2a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 1.9-1.4L22 8H6"></path>
                    <circle cx="10" cy="20" r="1"></circle>
                    <circle cx="18" cy="20" r="1"></circle>
                </svg>
                <span class="storefront-live-cart__count" data-live-cart-count>{{ array_sum(session('cart', [])) }}</span>
            </a>
        </div>
    </div>

    <footer class="site-footer">
        <div class="container footer-main">
            <div class="footer-brand">
                <a class="brand brand-light" href="{{ route('home') }}">
                    <img src="{{ $headerLogoUrl }}" alt="{{ $headerBrandName }}" class="brand-logo">
                </a>
                <p>{{ $siteHeaderFooter['footer_description'] }}</p>
            </div>
            <div class="footer-column">
                <h2>{{ $siteHeaderFooter['footer_explore_title'] }}</h2>
                <a href="{{ route('products.index') }}">{{ $siteHeaderFooter['footer_products_label'] }}</a>
                <a href="{{ route('about') }}">{{ $siteHeaderFooter['footer_story_label'] }}</a>
                <a href="{{ route('contact') }}">{{ $siteHeaderFooter['footer_visit_label'] }}</a>
            </div>
            <div class="footer-column">
                <h2>{{ $siteHeaderFooter['footer_connect_title'] }}</h2>
                <a href="mailto:{{ $siteHeaderFooter['footer_email'] }}">{{ $siteHeaderFooter['footer_email'] }}</a>
                <a href="tel:{{ preg_replace('/\s+/', '', $siteHeaderFooter['footer_phone']) }}">{{ $siteHeaderFooter['footer_phone'] }}</a>
                <span>{{ $siteHeaderFooter['footer_address'] }}</span>
            </div>
        </div>
        <div class="container footer-bottom">
            <span>© {{ date('Y') }} {{ $headerBrandName }}. {{ $siteHeaderFooter['footer_bottom_text'] }}</span>
            <a href="{{ route('contact') }}">{{ $siteHeaderFooter['footer_cta_label'] }} <span aria-hidden="true">↗</span></a>
        </div>
    </footer>
</body>
</html>
