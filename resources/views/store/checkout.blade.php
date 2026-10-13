@extends('layouts.store')

@section('title', 'Thanh toán')
@section('content')
    <section class="shopping-page">
        <div class="container">
            <div class="checkout-header">
                <div class="shopping-heading">
                    <span class="eyebrow">CHỈ CÒN MỘT BƯỚC NHỎ</span>
                    <h1>Thông tin <em>giao hàng.</em></h1>
                    <p>Hoàn tất đơn hàng của bạn một cách dễ dàng và an toàn.</p>
                </div>

                <div class="checkout-steps">
                    <div class="checkout-step is-completed">
                        <span class="checkout-step-number">✓</span>
                        <span>Giỏ hàng</span>
                    </div>
                    <div class="checkout-step-divider"></div>
                    <div class="checkout-step is-active">
                        <span class="checkout-step-number">2</span>
                        <span>Thông tin</span>
                    </div>
                    <div class="checkout-step-divider"></div>
                    <div class="checkout-step">
                        <span class="checkout-step-number">3</span>
                        <span>Xác nhận</span>
                    </div>
                </div>
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
                    <div class="checkout-section">
                        <h2>
                            <span class="section-icon">👤</span>
                            Người nhận hàng
                        </h2>
                        <label for="customer-name">Họ và tên</label>
                        <input id="customer-name" name="customer_name" type="text" value="{{ old('customer_name', auth()->user()->name) }}" autocomplete="name" required maxlength="120" placeholder="Nhập họ và tên của bạn">
                        @error('customer_name') <span class="form-error">{{ $message }}</span> @enderror

                        <label for="customer-email">Email</label>
                        <input id="customer-email" name="customer_email" type="email" value="{{ old('customer_email', auth()->user()->email) }}" autocomplete="email" required maxlength="255" placeholder="email@example.com">
                        @error('customer_email') <span class="form-error">{{ $message }}</span> @enderror

                        <label for="customer-phone">Số điện thoại</label>
                        <input id="customer-phone" name="customer_phone" type="tel" value="{{ old('customer_phone', auth()->user()->phone) }}" autocomplete="tel" required maxlength="30" placeholder="0xxx xxx xxx">
                        @error('customer_phone') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="checkout-section" data-vietnam-address>
                        <h2>
                            <span class="section-icon">📍</span>
                            Địa chỉ giao hàng
                        </h2>
                        <label for="shipping-province">Tỉnh/thành phố</label>
                        <select id="shipping-province" name="shipping_province" required data-vietnam-province
                            data-selected-province="{{ old('shipping_province', auth()->user()->shipping_province) }}">
                            <option value="">Chọn tỉnh/thành phố...</option>
                        </select>
                        @error('shipping_province') <span class="form-error">{{ $message }}</span> @enderror

                        <label for="shipping-ward">Phường/xã</label>
                        <select id="shipping-ward" name="shipping_ward" required data-vietnam-ward
                            data-selected-ward="{{ old('shipping_ward', auth()->user()->shipping_ward) }}" disabled>
                            <option value="">Chọn tỉnh/thành phố trước</option>
                        </select>
                        @error('shipping_ward') <span class="form-error">{{ $message }}</span> @enderror
                        <p class="form-error" data-address-error role="status" hidden></p>

                        <label for="shipping-address">Địa chỉ nhận hàng</label>
                        <textarea id="shipping-address" name="shipping_address" rows="3" autocomplete="street-address" required maxlength="500" placeholder="Số nhà, đường, phường/xã...">{{ old('shipping_address', auth()->user()->shipping_address) }}</textarea>
                        @error('shipping_address') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="checkout-section">
                        <h2>
                            <span class="section-icon">💳</span>
                            Phương thức thanh toán
                        </h2>
                        <div class="payment-methods">
                            <label class="payment-method-option">
                                <input type="radio" name="payment_method" value="cod" {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}>
                                <span class="payment-method-content">
                                    <span class="method-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                            <line x1="1" y1="10" x2="23" y2="10"></line>
                                        </svg>
                                    </span>
                                    <strong>Thanh toán khi nhận hàng (COD)</strong>
                                    <small>Trả tiền mặt cho shipper khi nhận hàng</small>
                                    <div class="payment-method-features">
                                        <span class="payment-method-feature">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            An toàn
                                        </span>
                                        <span class="payment-method-feature">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                            Tiện lợi
                                        </span>
                                    </div>
                                </span>
                            </label>
                            @if($bankTransferEnabled)
                                <label class="payment-method-option">
                                    <input type="radio" name="payment_method" value="bank_qr" {{ old('payment_method') === 'bank_qr' ? 'checked' : '' }}>
                                    <span class="payment-method-content">
                                        <span class="method-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="7" height="7"></rect>
                                                <rect x="14" y="3" width="7" height="7"></rect>
                                                <rect x="14" y="14" width="7" height="7"></rect>
                                                <rect x="3" y="14" width="7" height="7"></rect>
                                            </svg>
                                        </span>
                                        <strong>Chuyển khoản ngân hàng (QR)</strong>
                                        <small>Quét mã QR {{ $bankInfo['bank_name'] }} ({{ $bankInfo['bank_code'] }})</small>
                                        <div class="payment-method-features">
                                            <span class="payment-method-feature">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                Nhanh chóng
                                            </span>
                                            <span class="payment-method-feature">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                                Bảo mật
                                            </span>
                                        </div>
                                    </span>
                                </label>
                            @endif
                        </div>
                    </div>
                </div>

                <aside class="checkout-summary">
                    <div class="checkout-summary-header">
                        <h2>
                            <span class="cart-icon">🛒</span>
                            Đơn hàng của bạn
                        </h2>
                    </div>
                    <div class="checkout-summary-content">
                        @foreach ($items as $item)
                            <div class="checkout-line">
                                <span>{{ $item['product']->name }} × {{ $item['quantity'] }}</span>
                                <strong>{{ number_format($item['line_total'], 0, ',', '.') }}đ</strong>
                            </div>
                        @endforeach
                        <div class="checkout-line">
                            <span>Tạm tính</span>
                            <strong>{{ number_format($subtotal, 0, ',', '.') }}đ</strong>
                        </div>
                        <div class="checkout-line">
                            <span>Giao hàng</span>
                            <strong>{{ $shippingFee === 0 ? 'Miễn phí' : number_format($shippingFee, 0, ',', '.').'đ' }}</strong>
                        </div>
                        <div class="checkout-total">
                            <span>Tổng đơn hàng</span>
                            <strong>{{ number_format($subtotal + $shippingFee, 0, ',', '.') }}đ</strong>
                        </div>
                        <button class="checkout-button" type="submit" @disabled($items->contains(fn ($item) => ! $item['available']))>
                            ✓ Đặt hàng ngay
                        </button>
                        <a class="checkout-back-link" href="{{ route('cart.index') }}">← Quay lại giỏ hàng</a>
                    </div>
                </aside>
            </form>
        </div>
    </section>
@endsection
