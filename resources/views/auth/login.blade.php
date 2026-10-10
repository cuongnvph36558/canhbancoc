@extends('layouts.store')

@section('title', 'Đăng nhập')

@section('content')
    <section class="auth-shell">
        <div class="auth-card">
            <div class="auth-header">
                <span class="eyebrow"><span class="eyebrow-line"></span>Thành viên</span>
                <h1>Đăng nhập</h1>
                <p>Chào mừng bạn quay lại cửa hàng cốc xinh của chúng mình.</p>
            </div>

            @if ($errors->any())
                <div class="form-alert form-alert-error" role="alert">
                    @foreach ($errors->all() as $error)
                        <span>{{ $error }}</span>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <label class="field-group">
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
                </label>

                <label class="field-group">
                    <span>Mật khẩu</span>
                    <input type="password" name="password" placeholder="••••••••" required>
                </label>

                <label class="checkbox-row">
                    <input type="checkbox" name="remember" value="1">
                    <span>Ghi nhớ đăng nhập</span>
                </label>

                <button type="submit" class="button button-dark auth-submit">Đăng nhập</button>
            </form>

            <p class="auth-switch">
                Chưa có tài khoản?
                <a href="{{ route('register') }}">Đăng ký ngay</a>
            </p>
        </div>
    </section>
@endsection
