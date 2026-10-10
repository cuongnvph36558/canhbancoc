@extends('layouts.store')

@section('title', 'Giỏ hàng')
@section('content')
    <section class="shopping-page">
        <div class="container">
            <div class="shopping-heading">
                <span class="eyebrow">MÓN QUÀ ĐANG CHỜ BẠN</span>
                <h1>Giỏ hàng <em>xinh xắn.</em></h1>
                <p>Phí giao hàng 30.000đ, miễn phí cho đơn hàng từ 300.000đ.</p>
            </div>

            @if (session('success'))
                <p class="shopping-alert is-success" role="status">{{ session('success') }}</p>
            @endif
            @if (session('warning'))
                <p class="shopping-alert is-warning" role="status">{{ session('warning') }}</p>
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
                    <span aria-hidden="true">♡</span>
                    <h2>Giỏ hàng đang trống</h2>
                    <p>Chọn một món quà thật xinh để bắt đầu nhé.</p>
                    <a class="shopping-primary-button" href="{{ route('products.index') }}">Khám phá sản phẩm</a>
                </div>
            @else
                <div class="shopping-layout">
                    <div class="cart-items">
                        @foreach ($items as $item)
                            <article class="cart-item">
                                @if ($item['image'])
                                    <img src="{{ $item['image'] }}" alt="{{ $item['product']->name }}" loading="lazy">
                                @else
                                    <div class="cart-item-placeholder" aria-hidden="true">☕</div>
                                @endif
                                <div class="cart-item-details">
                                    <h2><a href="{{ route('products.show', $item['product']->slug) }}">{{ $item['product']->name }}</a></h2>
                                    <p>{{ number_format($item['unit_price'], 0, ',', '.') }}đ / sản phẩm</p>
                                    @if (! $item['available'])
                                        <p class="form-error">Chỉ còn {{ $item['product']->stock }} sản phẩm trong kho. Hãy cập nhật số lượng.</p>
                                    @endif
                                </div>
                                <form class="cart-quantity-form" method="POST" action="{{ route('cart.update', $item['product']->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <label class="visually-hidden" for="quantity-{{ $item['product']->id }}">Số lượng {{ $item['product']->name }}</label>
                                    <input id="quantity-{{ $item['product']->id }}" type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="99" required>
                                    <button type="submit">Cập nhật</button>
                                </form>
                                <strong class="cart-item-total">{{ number_format($item['line_total'], 0, ',', '.') }}đ</strong>
                                <form method="POST" action="{{ route('cart.destroy', $item['product']->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="cart-remove-button" type="submit" aria-label="Xóa {{ $item['product']->name }}">Xóa</button>
                                </form>
                            </article>
                        @endforeach
                    </div>

                    <aside class="shopping-summary">
                        <h2>Tóm tắt đơn hàng</h2>
                        <div><span>Tạm tính</span><strong>{{ number_format($subtotal, 0, ',', '.') }}đ</strong></div>
                        <div><span>Giao hàng</span><strong>{{ $subtotal >= 300000 ? 'Miễn phí' : '30.000đ' }}</strong></div>
                        <p>Phí giao hàng chính xác sẽ được xác nhận ở bước thanh toán.</p>
                        @if ($items->every(fn ($item) => $item['available']))
                            <a class="shopping-primary-button" href="{{ route('checkout.index') }}">Tiến hành thanh toán</a>
                        @else
                            <button class="shopping-primary-button" type="button" disabled>Cập nhật số lượng để tiếp tục</button>
                        @endif
                        <a class="shopping-secondary-link" href="{{ route('products.index') }}">Tiếp tục mua sắm</a>
                    </aside>
                </div>
            @endif
        </div>
    </section>
@endsection
