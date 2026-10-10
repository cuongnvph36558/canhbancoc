@extends('layouts.admin')

@section('title', 'Đơn cốc custom')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">THIẾT KẾ THEO YÊU CẦU</span>
            <h1>Đơn cốc custom</h1>
            <p>Xem thông tin giao hàng, thiết kế và hình in khách gửi kèm.</p>
        </div>
    </div>

    <div class="admin-summary">
        <div class="admin-stat-card">
            <span>Tổng đơn hàng</span>
            <strong>{{ $orders->total() }}</strong>
            <small>Tất cả đơn cốc custom</small>
        </div>
        <div class="admin-stat-card">
            <span>Chờ xác nhận</span>
            <strong>{{ $pendingCount }}</strong>
            <small>Đơn hàng cần liên hệ khách</small>
        </div>
    </div>

    <section class="admin-products-panel" aria-labelledby="custom-orders-heading">
        <div class="admin-panel-heading">
            <div>
                <h2 id="custom-orders-heading">Danh sách đơn hàng</h2>
                <p>Đơn mới nhất được hiển thị trước.</p>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Mẫu cốc</th>
                        <th>Số lượng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th class="admin-actions-heading">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                <img src="{{ route('admin.custom-cup-orders.preview', $order) }}" alt="Preview đơn {{ $order->order_number }}" class="admin-order-preview-thumb" loading="lazy">
                            </td>
                            <td><code>{{ $order->order_number }}</code></td>
                            <td>
                                <strong>{{ $order->customer_name }}</strong>
                                <small>{{ $order->customer_phone }}</small>
                            </td>
                            <td>{{ config("store.custom_cup.variants.{$order->cup_variant}.name") }} · {{ $order->cup_size }}</td>
                            <td>{{ $order->quantity }}</td>
                            <td><strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong></td>
                            <td><span class="admin-state is-inactive">{{ $statusLabels[$order->status] ?? $order->status }}</span></td>
                            <td><a href="{{ route('admin.custom-cup-orders.show', $order) }}" class="admin-action-button">Xem đơn hàng</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="admin-empty-state">Chưa có đơn cốc custom nào.</td>
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
