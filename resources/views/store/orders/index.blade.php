@extends('layouts.store')

@section('title', 'Đơn hàng của tôi')
@section('content')
    <section class="shopping-page customer-orders-page">
        <div class="container">
            <div class="shopping-heading">
                <span class="eyebrow">TÀI KHOẢN CỦA BẠN</span>
                <h1>Đơn hàng <em>của tôi.</em></h1>
                <p>Theo dõi đơn cốc thường và đơn cốc custom của bạn tại một nơi.</p>
            </div>

            <section class="customer-order-section" aria-labelledby="regular-orders-heading">
                <div class="customer-order-section-heading">
                    <div>
                        <span class="eyebrow">SẢN PHẨM CỐC MƠ</span>
                        <h2 id="regular-orders-heading">Đơn cốc thường</h2>
                    </div>
                    <span>{{ $orders->total() }} đơn</span>
                </div>

                <div class="customer-order-list">
                    @forelse ($orders as $order)
                        @php($firstItem = $order->items->first())
                        <article class="customer-order-card">
                            <div class="customer-order-image">
                                @if ($firstItem?->product_image)
                                    <img src="{{ $firstItem->product_image }}" alt="{{ $firstItem->product_name }}" loading="lazy">
                                @else
                                    <span aria-hidden="true">☕</span>
                                @endif
                            </div>
                            <div class="customer-order-info">
                                <div class="customer-order-meta">
                                    <span>Mã đơn <strong>{{ $order->order_number }}</strong></span>
                                    <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <h3>{{ $firstItem?->product_name ?? 'Đơn hàng cốc thường' }}{{ $order->items->count() > 1 ? ' và '.($order->items->count() - 1).' sản phẩm khác' : '' }}</h3>
                                <div class="customer-order-summary">
                                    <span class="customer-order-status">{{ $orderStatusLabels[$order->order_status] ?? $order->order_status }}</span>
                                    <span>
                                        @if ($order->payment_status === \App\Models\Order::PAYMENT_PAID)
                                            Đã thanh toán
                                        @elseif ($order->payment_method === \App\Models\Order::PAYMENT_METHOD_COD)
                                            Thanh toán khi nhận hàng (COD)
                                        @elseif ($order->payment_method === \App\Models\Order::PAYMENT_METHOD_BANK_QR)
                                            Chờ xác nhận chuyển khoản QR
                                        @else
                                            Chờ thanh toán qua VNPay
                                        @endif
                                    </span>
                                    <strong>{{ number_format((float) $order->total, 0, ',', '.') }}đ</strong>
                                </div>
                            </div>
                            <a class="customer-order-link" href="{{ route('orders.show', $order->order_number) }}">Xem đơn hàng <span aria-hidden="true">→</span></a>
                        </article>
                    @empty
                        <div class="customer-orders-empty">
                            <span aria-hidden="true">☕</span>
                            <p>Bạn chưa có đơn cốc thường nào.</p>
                            <a href="{{ route('products.index') }}">Khám phá sản phẩm</a>
                        </div>
                    @endforelse
                </div>

                @if ($orders->hasPages())
                    <div class="admin-pagination">{{ $orders->links() }}</div>
                @endif
            </section>

            <section class="customer-order-section" aria-labelledby="custom-orders-heading">
                <div class="customer-order-section-heading">
                    <div>
                        <span class="eyebrow">THIẾT KẾ RIÊNG</span>
                        <h2 id="custom-orders-heading">Đơn cốc custom</h2>
                    </div>
                    <span>{{ $customOrders->total() }} đơn</span>
                </div>

                <div class="customer-order-list">
                    @forelse ($customOrders as $order)
                        <article class="customer-order-card">
                            <div class="customer-order-image">
                                <img src="{{ route('customer.custom-cup-orders.preview', $order) }}" alt="Preview đơn {{ $order->order_number }}" loading="lazy">
                            </div>
                            <div class="customer-order-info">
                                <div class="customer-order-meta">
                                    <span>Mã đơn <strong>{{ $order->order_number }}</strong></span>
                                    <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <h3>Cốc {{ config("store.custom_cup.variants.{$order->cup_variant}.name", $order->cup_variant) }} · {{ $order->cup_size }}</h3>
                                <div class="customer-order-summary">
                                    <span class="customer-order-status">{{ $customOrderStatusLabels[$order->status] ?? $order->status }}</span>
                                    <span>{{ $order->quantity }} cốc · Đang chờ cửa hàng xác nhận</span>
                                    <strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong>
                                </div>
                            </div>
                            <a class="customer-order-link" href="{{ route('customer.custom-cup-orders.show', $order) }}">Theo dõi đơn <span aria-hidden="true">→</span></a>
                        </article>
                    @empty
                        <div class="customer-orders-empty">
                            <span aria-hidden="true">✦</span>
                            <p>Bạn chưa có đơn cốc custom nào.</p>
                            <a href="{{ route('products.customize') }}">Thiết kế chiếc cốc riêng</a>
                        </div>
                    @endforelse
                </div>

                @if ($customOrders->hasPages())
                    <div class="admin-pagination">{{ $customOrders->links() }}</div>
                @endif
            </section>
        </div>
    </section>
@endsection
