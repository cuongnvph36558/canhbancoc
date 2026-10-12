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

            <div class="customer-orders-controls">
                <form method="GET" action="{{ route('customer.orders.index') }}" class="customer-orders-search-form">
                    <label for="order-search" class="visually-hidden">Tìm kiếm đơn hàng</label>
                    <div class="customer-orders-search-input-wrapper">
                        <span class="customer-orders-search-icon" aria-hidden="true">🔍</span>
                        <input
                            type="text"
                            id="order-search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Tìm theo mã đơn hàng..."
                            autocomplete="off"
                        >
                        @if (request('q'))
                            <a href="{{ route('customer.orders.index', array_filter(request()->except('q'))) }}" class="customer-orders-search-clear" aria-label="Xóa tìm kiếm">×</a>
                        @endif
                    </div>
                    <button type="submit" class="customer-orders-search-btn">Tìm kiếm</button>
                </form>

                <form method="GET" action="{{ route('customer.orders.index') }}" class="customer-orders-filter-form" id="orders-filter-form">
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <div class="customer-orders-filter-row">
                        <label for="orders-status-filter" class="customer-orders-filter-label">Trạng thái:</label>
                        <select id="orders-status-filter" name="status" class="customer-orders-filter-select">
                            <option value="">Tất cả</option>
                            @foreach ($orderStatusLabels as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="customer-orders-filter-row">
                        <label for="orders-sort" class="customer-orders-filter-label">Sắp xếp:</label>
                        <select id="orders-sort" name="sort" class="customer-orders-filter-select">
                            <option value="newest" @selected(request('sort', 'newest') === 'newest')>Mới nhất</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>Giá cao → thấp</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>Giá thấp → cao</option>
                        </select>
                    </div>
                    @if (request('status') || request('sort', 'newest') !== 'newest' || request('q'))
                        <a href="{{ route('customer.orders.index') }}" class="customer-orders-clear-filters">Xóa lọc</a>
                    @endif
                </form>
            </div>

            @if (request('q') || request('status'))
                <p class="customer-orders-search-result">
                    @if (request('q'))
                        Kết quả tìm kiếm cho "<strong>{{ request('q') }}</strong>"
                        @if (request('status')) · @endif
                    @endif
                    @if (request('status'))
                        Lọc: <strong>{{ $orderStatusLabels[request('status')] ?? request('status') }}</strong>
                    @endif
                </p>
            @endif

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
                                            @else
                                                Thanh toán khi nhận hàng (COD)
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
                                <h3>Cốc {{ $cupVariants[$order->cup_variant]['name'] ?? $order->cup_variant }} · {{ $order->cup_size }}</h3>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('orders-filter-form');
        const statusSelect = document.getElementById('orders-status-filter');
        const sortSelect = document.getElementById('orders-sort');

        const submitFilterForm = () => {
            if (filterForm) {
                filterForm.submit();
            }
        };

        statusSelect?.addEventListener('change', submitFilterForm);
        sortSelect?.addEventListener('change', submitFilterForm);
    });
    </script>
@endsection
