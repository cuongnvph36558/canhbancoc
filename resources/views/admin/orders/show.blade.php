@extends('layouts.admin')

@section('title', 'Chi tiết đơn '.$order->order_number)

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">CHI TIẾT ĐƠN HÀNG</span>
            <h1>{{ $order->order_number }}</h1>
            <p>Đặt lúc {{ $order->created_at->format('H:i d/m/Y') }} · {{ $statusLabels[$order->order_status] ?? $order->order_status }}</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="admin-action-button">← Quay lại danh sách</a>
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
                <div><dt>Email</dt><dd><a href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a></dd></div>
                <div><dt>Địa chỉ</dt><dd>{{ collect([$order->shipping_address, $order->shipping_ward, $order->shipping_province])->filter()->implode(', ') }}</dd></div>
            </dl>
        </article>

        <article class="admin-products-panel">
            <div class="admin-panel-heading"><div><h2>Thanh toán & trạng thái</h2></div></div>
            <dl class="admin-order-details">
                <div><dt>Phương thức</dt><dd>{{ $paymentMethodLabel }}</dd></div>
                <div><dt>Thanh toán</dt><dd>{{ $paymentStatusLabel }}</dd></div>
                <div><dt>Trạng thái đơn</dt><dd>{{ $statusLabels[$order->order_status] ?? $order->order_status }}</dd></div>
                @if ($order->paid_at)
                    <div><dt>Đã thanh toán lúc</dt><dd>{{ $order->paid_at->format('H:i d/m/Y') }}</dd></div>
                @endif
            </dl>
            @if (
                $order->payment_method === \App\Models\Order::PAYMENT_METHOD_BANK_QR
                && $order->payment_status === \App\Models\Order::PAYMENT_PENDING
                && ! in_array($order->order_status, [
                    \App\Models\Order::STATUS_CANCELLED,
                    \App\Models\Order::STATUS_EXPIRED,
                    \App\Models\Order::STATUS_DELIVERED,
                ], true)
            )
                <form class="admin-order-status-form" method="POST" action="{{ route('admin.orders.confirm-bank-qr-payment', $order) }}">
                    @csrf
                    @method('PATCH')
                    <button class="admin-action-button" type="submit">Xác nhận đã nhận chuyển khoản</button>
                </form>
            @endif
            @if (! in_array($order->order_status, [\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_EXPIRED, \App\Models\Order::STATUS_DELIVERED], true))
                <form class="admin-order-status-form" method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                    @csrf
                    @method('PATCH')
                    <label for="order-status">Cập nhật trạng thái</label>
                    <select id="order-status" name="order_status">
                        @foreach ($editableStatuses as $value => $label)
                            <option value="{{ $value }}" @selected($order->order_status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="admin-action-button" type="submit">Lưu trạng thái</button>
                </form>
            @endif
        </article>

        <article class="admin-products-panel admin-order-design-panel">
            <div class="admin-panel-heading"><div><h2>Sản phẩm trong đơn</h2></div></div>
            <div class="admin-table-wrap">
                <table class="admin-table admin-orders-table">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Mã SKU</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>
                                    @if ($item->product_image)
                                        <img
                                            src="{{ $item->product_image }}"
                                            alt="{{ $item->product_name }}"
                                            class="admin-order-item-image"
                                            loading="lazy"
                                        >
                                    @else
                                        <span class="admin-order-item-placeholder" aria-label="Sản phẩm chưa có ảnh">☕</span>
                                    @endif
                                </td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->product_sku ?? '—' }}</td>
                                <td>{{ number_format((float) $item->unit_price, 0, ',', '.') }}đ</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format((float) $item->line_total, 0, ',', '.') }}đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <dl class="admin-order-details admin-order-totals">
                <div><dt>Tạm tính</dt><dd>{{ number_format((float) $order->subtotal, 0, ',', '.') }}đ</dd></div>
                <div><dt>Phí giao hàng</dt><dd>{{ (float) $order->shipping_fee === 0.0 ? 'Miễn phí' : number_format((float) $order->shipping_fee, 0, ',', '.') . 'đ' }}</dd></div>
                <div><dt>Tổng cộng</dt><dd><strong>{{ number_format((float) $order->total, 0, ',', '.') }}đ</strong></dd></div>
            </dl>
        </article>
    </section>
@endsection
