@extends('layouts.store')

@section('title', 'Giỏ hàng cốc tự thiết kế')
@section('content')
    <section class="shopping-page">
        <div class="container">
            <div class="shopping-heading">
                <span class="eyebrow">CỐC TỰ THIẾT KẾ</span>
                <h1>Giỏ hàng <em>thiết kế của bạn.</em></h1>
                <p>Mỗi chiếc cốc trong giỏ là một thiết kế riêng. Bạn có thể tiếp tục chỉnh sửa hoặc gửi yêu cầu đặt hàng.</p>
            </div>

            @if (session('success'))
                <p class="shopping-alert is-success" role="status">{{ session('success') }}</p>
            @endif
            @if ($errors->any())
                <div class="shopping-alert is-warning" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if ($items === [])
                <div class="empty-cart">
                    <span aria-hidden="true">✏️</span>
                    <h2>Chưa có thiết kế nào</h2>
                    <p>Hãy thử tự thiết kế cốc của bạn ngay nhé.</p>
                    <a class="shopping-primary-button" href="{{ route('products.customize') }}">Bắt đầu thiết kế</a>
                </div>
            @else
                <div class="shopping-layout">
                    <div class="cart-list-section">
                        <div class="cart-header">
                            <strong>Các thiết kế của bạn</strong>
                        </div>
                        <div class="cart-items">
                            @foreach ($items as $item)
                                <article class="cart-item">
                                    @if (! empty($item['preview']))
                                        <img src="{{ $item['preview'] }}" alt="Bản xem trước" loading="lazy">
                                    @else
                                        <div class="cart-item-placeholder" aria-hidden="true">☕</div>
                                    @endif
                                    <div class="cart-item-details">
                                        <h2>Cốc tự thiết kế — {{ $item['cup_variant_name'] }}</h2>
                                        <p>{{ number_format($item['unit_price'], 0, ',', '.') }}đ / sản phẩm · SL: {{ $item['quantity'] }}</p>
                                    </div>
                                    <strong class="cart-item-total">{{ number_format($item['line_total'], 0, ',', '.') }}đ</strong>
                                    <form method="POST" action="{{ route('custom-cup-cart.destroy', $item['id']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="cart-remove-button" type="submit" aria-label="Xóa">Xóa</button>
                                    </form>
                                </article>
                            @endforeach
                        </div>
                    </div>
                    <aside class="shopping-summary">
                        <h2>Tóm tắt đơn hàng</h2>
                        <div><span>Tạm tính</span><strong>{{ number_format($subtotal, 0, ',', '.') }}đ</strong></div>
                        <p>Bạn có thể quay lại trang thiết kế để gửi yêu cầu đặt hàng, hoặc xóa thiết kế không mong muốn.</p>
                        <a class="shopping-primary-button" href="{{ route('products.customize') }}">Về trang thiết kế</a>
                        <a class="shopping-secondary-link" href="{{ route('cart.index') }}">Xem giỏ hàng thường</a>
                    </aside>
                </div>
            @endif
        </div>
    </section>
@endsection
