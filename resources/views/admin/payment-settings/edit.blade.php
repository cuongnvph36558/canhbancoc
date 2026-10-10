@extends('layouts.admin')

@section('title', 'Cài đặt thanh toán QR')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">CÀI ĐẶT THANH TOÁN</span>
            <h1>Thanh toán QR ngân hàng</h1>
            <p>Quản lý tài khoản nhận tiền. Mã QR riêng của mỗi đơn sẽ tự điền số tiền và nội dung chuyển khoản.</p>
        </div>
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

    <form method="POST" action="{{ route('admin.payment-settings.update') }}" class="admin-content-editor">
        @csrf
        @method('PUT')

        <section class="admin-content-section">
            <h2>Thông tin tài khoản nhận tiền</h2>
            <div class="admin-content-fields">
                <div class="admin-content-field">
                    <label for="bank_name">Tên ngân hàng</label>
                    <input id="bank_name" name="bank_name" type="text" value="{{ old('bank_name', $paymentSetting?->bank_name) }}" maxlength="120" required>
                    @error('bank_name') <small class="admin-content-error">{{ $message }}</small> @enderror
                </div>
                <div class="admin-content-field">
                    <label for="bank_code">Mã ngân hàng</label>
                    <input id="bank_code" name="bank_code" type="text" value="{{ old('bank_code', $paymentSetting?->bank_code) }}" maxlength="20" required>
                    <small>Mã ngân hàng dùng để đối chiếu với mã QR.</small>
                    @error('bank_code') <small class="admin-content-error">{{ $message }}</small> @enderror
                </div>
                <div class="admin-content-field">
                    <label for="account_number">Số tài khoản</label>
                    <input id="account_number" name="account_number" type="text" value="{{ old('account_number', $paymentSetting?->account_number) }}" maxlength="50" required>
                    @error('account_number') <small class="admin-content-error">{{ $message }}</small> @enderror
                </div>
                <div class="admin-content-field">
                    <label for="account_name">Tên chủ tài khoản</label>
                    <input id="account_name" name="account_name" type="text" value="{{ old('account_name', $paymentSetting?->account_name) }}" maxlength="120" required>
                    @error('account_name') <small class="admin-content-error">{{ $message }}</small> @enderror
                </div>
                <div class="admin-content-field">
                    <label for="is_enabled">Trạng thái thanh toán QR</label>
                    <div class="admin-payment-toggle">
                        <input id="is_enabled" type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $paymentSetting?->is_enabled ?? false))>
                        <label for="is_enabled">Bật thanh toán chuyển khoản QR ở trang thanh toán</label>
                    </div>
                    <small>Khi quét mã, ứng dụng ngân hàng sẽ tự điền số tiền đơn hàng và mã đơn làm nội dung chuyển khoản.</small>
                </div>
            </div>
        </section>

        <div class="admin-content-actions">
            <button type="submit" class="admin-btn-primary">Lưu cài đặt thanh toán</button>
        </div>
    </form>
@endsection
