<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Khu vực quản trị Cốc Mơ">
    <title>@yield('title', 'Quản trị') — Cốc Mơ Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-panel" data-realtime-role="admin" data-realtime-user-id="{{ auth()->id() }}">
    <div class="admin-shell">
        <aside class="admin-sidebar" id="admin-sidebar">
            <a class="admin-brand" href="{{ route('admin.users') }}">
                <span class="admin-brand-content">
                    <img src="{{ asset('img/logo.png').'?v='.filemtime(public_path('img/logo.png')) }}" alt="Cốc Xinh" class="admin-brand-logo">
                    <small>TRANG QUẢN TRỊ</small>
                </span>
            </a>

            <div class="admin-nav-label">QUẢN LÝ</div>
            <nav class="admin-navigation" aria-label="Điều hướng quản trị">
                <a @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.pages.*') && request()->route('page') === 'home'])
                   href="{{ route('admin.pages.edit', 'home') }}">
                    <span aria-hidden="true">⌂</span>
                    Nội dung trang chủ
                </a>
                <a @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.pages.*') && request()->route('page') === 'about'])
                   href="{{ route('admin.pages.edit', 'about') }}">
                    <span aria-hidden="true">✎</span>
                    Nội dung giới thiệu
                </a>
                <a @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.products.*')])
                   href="{{ route('admin.products.index') }}">
                    <span aria-hidden="true">📦</span>
                    Sản phẩm & kho hàng
                </a>
                <a @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.orders.*')])
                   href="{{ route('admin.orders.index') }}">
                    <span aria-hidden="true">🧾</span>
                    Đơn hàng cốc thường
                </a>
                <a @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.payment-settings.*')])
                   href="{{ route('admin.payment-settings.edit') }}">
                    <span aria-hidden="true">▦</span>
                    Thanh toán QR
                </a>
                <a @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.custom-cup-orders.*')])
                   href="{{ route('admin.custom-cup-orders.index') }}">
                    <span aria-hidden="true">☕</span>
                    Đơn cốc custom
                </a>
                <a @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.contact-inquiries.*')])
                   href="{{ route('admin.contact-inquiries.index') }}">
                    <span aria-hidden="true">✉</span>
                    Lời nhắn liên hệ
                </a>
                <a @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.chat.*')])
                   href="{{ route('admin.chat.index') }}">
                    <span aria-hidden="true">◉</span>
                    Messenger
                </a>
                <a @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.users')])
                   href="{{ route('admin.users') }}">
                    <span aria-hidden="true">♙</span>
                    Khách hàng & phân quyền
                </a>
            </nav>

            <div class="admin-sidebar-bottom">
                <a href="{{ route('home') }}">← Xem trang client</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Đăng xuất</button>
                </form>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar-leading">
                    <button class="admin-sidebar-toggle" type="button" aria-expanded="false" aria-controls="admin-sidebar" aria-label="Mở menu quản trị">☰</button>
                    <div>
                        <span class="admin-topbar-eyebrow">KHU VỰC QUẢN TRỊ</span>
                        <p>Xin chào, <strong>{{ auth()->user()->name }}</strong></p>
                    </div>
                </div>
                <a class="admin-view-store" href="{{ route('home') }}">Mở trang client <span aria-hidden="true">↗</span></a>
            </header>

            <main class="admin-content">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
