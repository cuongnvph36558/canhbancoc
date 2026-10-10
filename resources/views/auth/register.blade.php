@extends('layouts.store')

@section('title', 'Đăng ký')

@section('content')
    <section class="auth-shell">
        <div class="auth-card">
            <div class="auth-header">
                <span class="eyebrow"><span class="eyebrow-line"></span>Tạo tài khoản</span>
                <h1>Đăng ký</h1>
                <p>Tham gia để lưu yêu thích và đặt hàng theo phong cách riêng của bạn.</p>
            </div>

            @if ($errors->any())
                <div class="form-alert form-alert-error" role="alert">
                    @foreach ($errors->all() as $error)
                        <span>{{ $error }}</span>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf

                <label class="field-group">
                    <span>Họ tên</span>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Nguyễn Văn A" required autofocus>
                </label>

                <label class="field-group">
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required>
                </label>

                <label class="field-group">
                    <span>Số điện thoại</span>
                    <input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="09xx xxx xxx" required minlength="8" maxlength="30">
                </label>

                <div class="field-group" data-vietnam-address>
                    <label for="register-shipping-province">Tỉnh/thành phố</label>
                    <select id="register-shipping-province" name="shipping_province" required data-vietnam-province
                        data-selected-province="{{ old('shipping_province') }}">
                        <option value="">Đang tải danh sách tỉnh/thành...</option>
                    </select>
                    @error('shipping_province') <span class="form-error">{{ $message }}</span> @enderror

                    <label for="register-shipping-ward">Phường/xã</label>
                    <select id="register-shipping-ward" name="shipping_ward" required data-vietnam-ward
                        data-selected-ward="{{ old('shipping_ward') }}" disabled>
                        <option value="">Chọn tỉnh/thành phố trước</option>
                    </select>
                    @error('shipping_ward') <span class="form-error">{{ $message }}</span> @enderror
                    <p class="form-error" data-address-error role="status" hidden></p>
                </div>

                <label class="field-group">
                    <span>Địa chỉ chi tiết</span>
                    <textarea name="shipping_address" autocomplete="street-address" placeholder="Số nhà, tên đường..." required maxlength="500">{{ old('shipping_address') }}</textarea>
                    @error('shipping_address') <span class="form-error">{{ $message }}</span> @enderror
                </label>

                <label class="field-group">
                    <span>Mật khẩu</span>
                    <input type="password" name="password" placeholder="Ít nhất 8 ký tự" required>
                </label>

                <label class="field-group">
                    <span>Nhập lại mật khẩu</span>
                    <input type="password" name="password_confirmation" placeholder="Xác nhận mật khẩu" required>
                </label>

                <button type="submit" class="button button-dark auth-submit">Tạo tài khoản</button>
            </form>

            <p class="auth-switch">
                Đã có tài khoản?
                <a href="{{ route('login') }}">Đăng nhập</a>
            </p>
        </div>
    </section>
@endsection
