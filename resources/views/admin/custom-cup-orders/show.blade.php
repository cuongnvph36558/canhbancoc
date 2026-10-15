@extends('layouts.admin')

@section('title', 'Chi tiết đơn '.$order->order_number)

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">ĐƠN CỐC CUSTOM</span>
            <h1>{{ $order->order_number }}</h1>
            <p>Đơn đặt lúc {{ $order->created_at->format('H:i d/m/Y') }} · {{ $statusLabels[$order->status] ?? $order->status }}</p>
        </div>
        <a href="{{ route('admin.custom-cup-orders.index') }}" class="admin-action-button">← Quay lại danh sách</a>
        <a href="{{ route('admin.custom-cup-orders.print', $order) }}" target="_blank" rel="noopener" class="admin-action-button">⬇ Tải thiết kế (SVG/PNG)</a>
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

    <section class="admin-order-detail-grid">
        <article class="admin-products-panel">
            <div class="admin-panel-heading"><div><h2>Thông tin giao hàng</h2></div></div>
            <dl class="admin-order-details">
                <div><dt>Người nhận</dt><dd>{{ $order->customer_name }}</dd></div>
                <div><dt>Số điện thoại</dt><dd><a href="tel:{{ $order->customer_phone }}">{{ $order->customer_phone }}</a></dd></div>
                @if ($order->customer_email)
                    <div><dt>Email</dt><dd><a href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a></dd></div>
                @endif
                <div><dt>Địa chỉ</dt><dd>{{ collect([$order->shipping_address, $order->shipping_ward, $order->shipping_province])->filter()->implode(', ') }}</dd></div>
            </dl>
        </article>

        <article class="admin-products-panel">
            <div class="admin-panel-heading"><div><h2>Chi tiết sản phẩm</h2></div></div>
            <dl class="admin-order-details">
                <div>
                    <dt>Kiểu cốc</dt>
                    <dd>
                        {{ $cupVariant['name'] ?? $order->cup_variant }} · {{ $order->cup_size }}
                        @if ($cupVariant)
                            <span class="admin-cup-color-preview">
                                <span class="cup-color-swatch" style="background-color: {{ $cupVariant['handle_color'] }}" title="Màu quai: {{ $cupVariant['handle_color'] }}"></span>
                                <span class="cup-color-swatch" style="background-color: {{ $cupVariant['interior_color'] }}" title="Màu lòng: {{ $cupVariant['interior_color'] }}"></span>
                            </span>
                        @endif
                    </dd>
                </div>
                <div><dt>Số lượng</dt><dd>{{ $order->quantity }} cốc</dd></div>
                <div><dt>Đơn giá</dt><dd>{{ number_format($order->unit_price, 0, ',', '.') }}đ</dd></div>
                <div><dt>Giảm giá</dt><dd>{{ $order->discount_percentage }}%</dd></div>
                <div><dt>Tổng cộng</dt><dd><strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong></dd></div>
            </dl>
            @if (! in_array($order->status, ['delivered', 'completed', 'cancelled'], true))
                <form class="admin-order-status-form" method="POST" action="{{ route('admin.custom-cup-orders.update-status', $order) }}">
                    @csrf
                    @method('PATCH')
                    <label for="custom-order-status">Cập nhật trạng thái</label>
                    <select id="custom-order-status" name="status">
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="admin-action-button" type="submit">Lưu trạng thái</button>
                </form>
            @endif
        </article>

        <article class="admin-products-panel admin-order-design-panel">
            <div class="admin-panel-heading"><div><h2>Thiết kế in trên cốc</h2></div></div>
            <div class="admin-order-design-items">
                @foreach ($order->design['items'] ?? [] as $item)
                    <span class="admin-order-design-item">
                        @if ($item['type'] === 'image' && $order->artwork_path)
                            Hình khách tải lên
                        @elseif ($item['type'] === 'icon')
                            {{ $item['content'] }}
                        @else
                            {{ $item['content'] }}
                        @endif
                    </span>
                @endforeach
            </div>
            @if ($order->artwork_path)
                <a class="admin-action-button" href="{{ route('admin.custom-cup-orders.artwork', $order) }}">Tải hình in khách gửi</a>
            @endif
            @php
                $artworkPaths = $order->artwork_paths ?? ($order->artwork_path ? [$order->artwork_path] : []);
            @endphp
            @if (count($artworkPaths) > 0)
                <div class="admin-order-artwork-gallery">
                    <h3>Hình in khách gửi ({{ count($artworkPaths) }} ảnh)</h3>
                    <div class="admin-artwork-images">
                        @foreach ($artworkPaths as $index => $artworkPath)
                            <div class="admin-artwork-image-item">
                                <img src="{{ route('admin.custom-cup-orders.artwork', ['order' => $order, 'index' => $index]) }}" alt="Hình in {{ $index + 1 }}">
                                <span>Hình {{ $index + 1 }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="admin-order-preview">
                <img src="{{ route('admin.custom-cup-orders.preview', $order) }}" alt="Preview cốc custom {{ $order->order_number }}" loading="lazy">
            </div>
            <a class="admin-action-button" href="{{ route('admin.custom-cup-orders.preview', $order) }}" target="_blank" rel="noopener">Xem ảnh cốc custom</a>
        </article>
    </section>
@endsection
