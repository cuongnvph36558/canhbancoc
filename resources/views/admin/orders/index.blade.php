@extends('layouts.admin')

@section('title', 'Đơn hàng cốc thường')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">ĐƠN HÀNG SẢN PHẨM</span>
            <h1>Đơn hàng cốc thường</h1>
            <p>Tìm kiếm đơn, theo dõi giao hàng và kiểm tra thanh toán.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success" role="status">{{ session('success') }}</div>
    @endif

    <div class="admin-summary">
        <div class="admin-stat-card">
            <span>Tổng đơn</span>
            <strong>{{ $totalOrders }}</strong>
            <small>Tất cả đơn cốc thường</small>
        </div>
        <div class="admin-stat-card">
            <span>Chờ xử lý</span>
            <strong>{{ $pendingOrders }}</strong>
            <small>Đơn mới cần xác nhận</small>
        </div>
        <div class="admin-stat-card">
            <span>COD chưa thu</span>
            <strong>{{ $unpaidCodOrders }}</strong>
            <small>Thanh toán khi nhận hàng</small>
        </div>
    </div>

    <section class="admin-products-panel" aria-labelledby="orders-heading">
        <div class="admin-panel-heading admin-order-list-heading">
            <div>
                <h2 id="orders-heading">Danh sách đơn hàng</h2>
                <p>Đơn mới nhất được hiển thị trước.</p>
            </div>
            <form class="admin-order-filters" method="GET" action="{{ route('admin.orders.index') }}">
                <label class="sr-only" for="order-search">Tìm theo mã đơn hoặc thông tin khách hàng</label>
                <input id="order-search" type="search" name="search" value="{{ $search }}" placeholder="Mã đơn, tên, email, số điện thoại">
                <label class="sr-only" for="order-status-filter">Lọc theo trạng thái</label>
                <select id="order-status-filter" name="status">
                    <option value="">Tất cả trạng thái</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="admin-action-button" type="submit">Lọc đơn</button>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table admin-orders-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Ảnh / sản phẩm</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thanh toán</th>
                        <th>Ngày đặt</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td>
                                <strong>{{ $order->customer_name }}</strong>
                                <small>{{ $order->customer_phone }}</small>
                            </td>
                            <td>
                                @php($firstItem = $order->items->first())
                                @if ($firstItem?->product_image)
                                    <img
                                        src="{{ $firstItem->product_image }}"
                                        alt="{{ $firstItem->product_name }}"
                                        class="admin-order-item-thumb"
                                        loading="lazy"
                                    >
                                @else
                                    <span class="admin-order-item-placeholder" aria-label="Sản phẩm chưa có ảnh">☕</span>
                                @endif
                                <small>{{ $firstItem?->product_name }}{{ $order->items_count > 1 ? ' và '.($order->items_count - 1).' sản phẩm khác' : '' }}</small>
                            </td>
                            <td><strong>{{ number_format((float) $order->total, 0, ',', '.') }}đ</strong></td>
                            <td><span class="admin-state is-inactive">{{ $statusLabels[$order->order_status] ?? $order->order_status }}</span></td>
                            <td>
                                @if ($order->payment_method === \App\Models\Order::PAYMENT_METHOD_COD)
                                    COD
                                @elseif ($order->payment_method === \App\Models\Order::PAYMENT_METHOD_BANK_QR)
                                    QR ngân hàng
                                @else
                                    VNPay
                                @endif
                                <small>
                                    @if ($order->payment_status === \App\Models\Order::PAYMENT_PAID)
                                        Đã thanh toán
                                    @elseif ($order->payment_status === \App\Models\Order::PAYMENT_FAILED)
                                        Thất bại
                                    @else
                                        Chưa thanh toán
                                    @endif
                                </small>
                            </td>
                            <td>{{ $order->created_at->format('H:i d/m/Y') }}</td>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="admin-action-button">Chi tiết</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="admin-empty-state">Không tìm thấy đơn hàng phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            {{ $orders->links() }}
        </div>
    </section>
@endsection
