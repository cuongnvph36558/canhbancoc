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
                && $bankQrImageUrl
            )
                <div class="admin-payment-qr-card">
                    <div class="admin-panel-heading"><div><h3>Mã QR chuyển khoản</h3></div></div>
                    <img src="{{ $bankQrImageUrl }}" alt="Mã QR thanh toán ngân hàng" class="admin-payment-qr-image">
                    <p>{{ $order->payment_bank_name ?? 'Ngân hàng' }} · {{ $order->payment_account_number }}</p>
                    <small>Chủ tài khoản: {{ $order->payment_account_name }}</small>
                </div>
            @endif
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
                                <td>
                                    {{ $item->product_name }}
                                    @if ($item->custom_text)
                                        <div class="admin-custom-info">
                                            <strong>Text in:</strong> {{ $item->custom_text }}
                                        </div>
                                    @endif
                                    @if ($item->custom_color)
                                        @php($colorValue = $item->custom_color)
                                        @php($isHex = (bool) preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $colorValue))
                                        <div class="admin-custom-info admin-custom-color">
                                            <span class="admin-color-swatch admin-color-swatch-lg" style="background-color: {{ $colorValue }};"></span>
                                            <span><strong>{{ $isHex ? 'Mã màu' : 'Màu' }}:</strong> {{ $colorValue }}</span>
                                        </div>
                                    @elseif ($item->product?->colors?->isNotEmpty())
                                        @php($defaultColor = $item->product->colors->first())
                                        @php($colorValue = $defaultColor->hex_code)
                                        @php($colorName  = $defaultColor->name)
                                        <div class="admin-custom-info admin-custom-color">
                                            <span class="admin-color-swatch admin-color-swatch-lg" style="background-color: {{ $colorValue }};"></span>
                                            <span><strong>Màu mặc định:</strong> {{ $colorName }} ({{ $colorValue }})</span>
                                        </div>
                                    @endif
                                    @if ($item->custom_image_path)
                                        <div class="admin-custom-info">
                                            <strong>Ảnh in:</strong>
                                            <a href="{{ Storage::url($item->custom_image_path) }}" target="_blank">
                                                📎 Xem ảnh
                                            </a>
                                        </div>
                                    @endif
                                </td>
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

@push('styles')
<style>
/* ==== Trang chi tiết đơn hàng: chữ to, dễ nhìn hơn ==== */
.admin-page-heading h1 {
    font-size: clamp(28px, 2.6vw, 34px);
}
.admin-page-heading p {
    font-size: 14px;
    margin-top: 6px;
}

.admin-order-detail-grid {
    grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr);
    gap: 22px;
}

.admin-order-detail-grid .admin-products-panel {
    padding: 0;
}

.admin-order-detail-grid .admin-panel-heading {
    padding: 20px 24px;
}

.admin-order-detail-grid .admin-panel-heading h2 {
    font-size: 18px;
    margin-bottom: 6px;
}

.admin-order-detail-grid .admin-panel-heading p {
    font-size: 13px;
}

.admin-order-details {
    gap: 14px;
    padding: 22px 24px 24px;
    font-size: 14px;
}

.admin-order-details > div {
    grid-template-columns: minmax(140px, .42fr) minmax(0, 1fr);
    gap: 16px;
    padding: 12px 14px;
    border: 1px solid #edf2f6;
    border-radius: 10px;
    background: #f9fbfd;
    font-size: 14px;
    align-items: center;
}

.admin-order-details dt {
    color: #4a6178;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.admin-order-details dd {
    color: #1f2c3d;
    font-size: 15px;
    font-weight: 500;
    line-height: 1.5;
}

.admin-order-details dd a {
    color: #1f6fb8;
    font-weight: 500;
}

.admin-order-totals {
    max-width: 520px;
    margin-left: auto;
    margin-right: auto;
    padding: 22px 26px 26px;
    border-top: 1px solid #edf2f6;
    gap: 14px;
    background: #fbfdff;
    border-radius: 12px;
    border: 1px solid #e1edf5;
}

.admin-order-totals > div {
    padding: 12px 16px;
    border: 1px solid #e3edf3;
    border-radius: 10px;
    background: #fff;
    font-size: 15px;
}

.admin-order-totals > div:last-child {
    background: #f0f7ff;
    border-color: #cfe2f1;
}

.admin-order-totals dt {
    color: #4a6178;
    font-size: 13px;
}

.admin-order-totals dd {
    color: #1f2c3d;
    font-size: 16px;
    font-weight: 600;
}

.admin-order-totals > div:last-child dd {
    font-size: 19px;
    font-weight: 800;
    color: #1f6fb8;
}

/* Bảng sản phẩm to rõ */
.admin-orders-table {
    min-width: 920px;
    font-size: 14px;
}

.admin-orders-table th,
.admin-orders-table td {
    padding: 14px 16px;
    font-size: 14px;
}

.admin-orders-table thead th {
    font-size: 11px;
    padding: 14px 16px;
}

.admin-orders-table td {
    color: #1f2c3d;
}

.admin-orders-table td strong {
    font-weight: 700;
    font-size: 14px;
}

.admin-order-item-image,
.admin-order-item-placeholder {
    width: 72px;
    height: 72px;
}

.admin-order-item-placeholder {
    font-size: 26px;
}

/* Form cập nhật trạng thái: gọn, dễ bấm */
.admin-order-status-form {
    gap: 12px;
    padding: 18px 24px 22px;
    border-top: 1px solid #edf2f6;
    background: #fbfdff;
}

.admin-order-status-form label {
    flex-basis: auto;
    font-size: 13px;
    font-weight: 700;
}

.admin-order-status-form select {
    min-height: 42px;
    font-size: 14px;
    padding: 0 12px;
    border-radius: 9px;
    flex: 1;
    min-width: 180px;
}

.admin-order-status-form .admin-action-button {
    min-height: 42px;
    padding: 0 20px;
    font-size: 13px;
    border-radius: 9px;
}

/* QR card */
.admin-payment-qr-card {
    margin: 0 24px 22px;
    padding: 20px;
    border: 1px solid #e1edf5;
    border-radius: 12px;
    background: #f9fbfd;
    text-align: center;
}

.admin-payment-qr-card h3 {
    font-size: 14px;
    margin-bottom: 12px;
    color: #1f2c3d;
}

.admin-payment-qr-card p,
.admin-payment-qr-card small {
    font-size: 13px;
    color: #4a6178;
}

.admin-payment-qr-image {
    width: min(220px, 100%);
    height: auto;
    border-radius: 10px;
    margin-bottom: 12px;
}

/* Badge tag thông tin custom (text in, màu, ảnh) */
.admin-custom-info {
    font-size: 13px;
    padding: 6px 10px;
    margin-top: 8px;
}

.admin-custom-color {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    max-width: 100%;
}

.admin-color-swatch {
    display: inline-block;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 1px solid rgba(0, 0, 0, .15);
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, .12);
}

.admin-color-swatch-lg {
    width: 22px;
    height: 22px;
}

@media (max-width: 900px) {
    .admin-order-detail-grid {
        grid-template-columns: minmax(0, 1fr);
    }
    .admin-order-details > div {
        grid-template-columns: minmax(0, 1fr);
        gap: 4px;
    }
}
</style>
@endpush
