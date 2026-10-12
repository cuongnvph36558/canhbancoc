@extends('layouts.store')

@section('title', 'Thanh toán')
@section('content')
    <section class="shopping-page">
        <div class="container">
            <div class="shopping-heading">
                <span class="eyebrow">CHỈ CÒN MỘT BƯỚC NHỎ</span>
                <h1>Thông tin <em>giao hàng.</em></h1>
                <p>Đăng nhập để đặt hàng và thanh toán khi nhận hàng.</p>
            </div>

            @if ($errors->any())
                <div class="shopping-alert is-warning" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form class="checkout-layout" method="POST" action="{{ route('checkout.store') }}">
                @csrf
                <div class="checkout-fields">
                    <h2>Người nhận hàng</h2>
                    <label for="customer-name">Họ và tên</label>
                    <input id="customer-name" name="customer_name" type="text" value="{{ old('customer_name', auth()->user()->name) }}" autocomplete="name" required maxlength="120">
                    @error('customer_name') <span class="form-error">{{ $message }}</span> @enderror

                    <label for="customer-email">Email</label>
                    <input id="customer-email" name="customer_email" type="email" value="{{ old('customer_email', auth()->user()->email) }}" autocomplete="email" required maxlength="255">
                    @error('customer_email') <span class="form-error">{{ $message }}</span> @enderror

                    <label for="customer-phone">Số điện thoại</label>
                    <input id="customer-phone" name="customer_phone" type="tel" value="{{ old('customer_phone', auth()->user()->phone) }}" autocomplete="tel" required maxlength="30">
                    @error('customer_phone') <span class="form-error">{{ $message }}</span> @enderror

                    <div data-vietnam-address>
                        <label for="shipping-province">Tỉnh/thành phố</label>
                        <select id="shipping-province" name="shipping_province" required data-vietnam-province
                            data-selected-province="{{ old('shipping_province', auth()->user()->shipping_province) }}">
                            <option value="">Đang tải danh sách tỉnh/thành...</option>
                        </select>
                        @error('shipping_province') <span class="form-error">{{ $message }}</span> @enderror

                        <label for="shipping-ward">Phường/xã</label>
                        <select id="shipping-ward" name="shipping_ward" required data-vietnam-ward
                            data-selected-ward="{{ old('shipping_ward', auth()->user()->shipping_ward) }}" disabled>
                            <option value="">Chọn tỉnh/thành phố trước</option>
                        </select>
                        @error('shipping_ward') <span class="form-error">{{ $message }}</span> @enderror
                        <p class="form-error" data-address-error role="status" hidden></p>
                    </div>

                    <label for="shipping-address">Địa chỉ nhận hàng</label>
                    <textarea id="shipping-address" name="shipping_address" rows="4" autocomplete="street-address" required maxlength="500">{{ old('shipping_address', auth()->user()->shipping_address) }}</textarea>
                    @error('shipping_address') <span class="form-error">{{ $message }}</span> @enderror
                    <input type="hidden" name="payment_method" value="cod">
                    <p class="payment-note">Hiện tại cửa hàng chỉ nhận thanh toán khi nhận hàng (COD). Bạn sẽ thanh toán trực tiếp cho shipper khi nhận đơn.</p>
                </div>

                <aside class="shopping-summary">
                    <h2>Đơn hàng của bạn</h2>
                    @foreach ($items as $item)
                        <div class="checkout-line">
                            <span>{{ $item['product']->name }} × {{ $item['quantity'] }}</span>
                            <strong>{{ number_format($item['line_total'], 0, ',', '.') }}đ</strong>
                        </div>
                    @endforeach
                    <div><span>Tạm tính</span><strong>{{ number_format($subtotal, 0, ',', '.') }}đ</strong></div>
                    <div><span>Giao hàng</span><strong>{{ $shippingFee === 0 ? 'Miễn phí' : number_format($shippingFee, 0, ',', '.').'đ' }}</strong></div>
                    <div class="summary-total"><span>Tổng đơn hàng</span><strong>{{ number_format($subtotal + $shippingFee, 0, ',', '.') }}đ</strong></div>
                    <button class="shopping-primary-button" type="submit" @disabled($items->contains(fn ($item) => ! $item['available']))>Đặt hàng</button>
                    <a class="shopping-secondary-link" href="{{ route('cart.index') }}">Quay lại giỏ hàng</a>
                </aside>
            </form>
        </div>
    </section>
@endsection
