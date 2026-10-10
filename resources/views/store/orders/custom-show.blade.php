@extends('layouts.store')

@section('title', 'Theo dõi đơn '.$order->order_number)
@section('content')
    <section class="shopping-page customer-orders-page">
        <div class="container">
            <div class="shopping-heading">
                <span class="eyebrow">ĐƠN CỐC CUSTOM</span>
                <h1>Theo dõi <em>đơn hàng.</em></h1>
                <p>Mã đơn <strong>{{ $order->order_number }}</strong> · Đặt lúc {{ $order->created_at->format('H:i d/m/Y') }}</p>
            </div>

            <a class="customer-orders-back" href="{{ route('customer.orders.index') }}">← Quay lại tất cả đơn hàng</a>

            <div class="customer-custom-order-detail">
                <article class="customer-custom-order-preview">
                    @if ($previewUrl)
                        <img src="{{ $previewUrl }}" alt="Thiết kế cốc custom {{ $order->order_number }}">
                    @else
                        <span aria-hidden="true">☕</span>
                        <p>Chưa có ảnh xem trước cho đơn hàng này.</p>
                    @endif
                </article>

                <article class="customer-custom-order-information">
                    <span class="customer-order-status">{{ $statusLabel }}</span>
                    <h2>Thông tin đơn hàng</h2>
                    <dl>
                        <div><dt>Kiểu cốc</dt><dd>{{ $cupVariant['name'] ?? $order->cup_variant }} · {{ $order->cup_size }}</dd></div>
                        <div><dt>Số lượng</dt><dd>{{ $order->quantity }} cốc</dd></div>
                        <div><dt>Tạm tính</dt><dd>{{ number_format($order->unit_price * $order->quantity, 0, ',', '.') }}đ</dd></div>
                        @if ($order->discount_percentage > 0)
                            <div><dt>Ưu đãi</dt><dd>Giảm {{ $order->discount_percentage }}%</dd></div>
                        @endif
                        <div class="customer-custom-order-total"><dt>Tổng cộng</dt><dd>{{ number_format($order->total_amount, 0, ',', '.') }}đ</dd></div>
                    </dl>

                    <h3>Thông tin giao hàng</h3>
                    <p>{{ $order->customer_name }} · <a href="tel:{{ $order->customer_phone }}">{{ $order->customer_phone }}</a></p>
                    <p>{{ collect([$order->shipping_address, $order->shipping_ward, $order->shipping_province])->filter()->implode(', ') }}</p>

                    <h3>Thiết kế trên cốc</h3>
                    <div class="customer-custom-order-design">
                        @foreach ($order->design['items'] ?? [] as $item)
                            <span>
                                @if (($item['type'] ?? null) === 'image')
                                    Hình khách tải lên
                                @else
                                    {{ $item['content'] ?? '' }}
                                @endif
                            </span>
                        @endforeach
                    </div>

                    <p class="customer-custom-order-note">Cửa hàng sẽ liên hệ bạn để xác nhận chi tiết đơn hàng và cách thanh toán.</p>
                </article>
            </div>
        </div>
    </section>
@endsection
