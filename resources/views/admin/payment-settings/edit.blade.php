@extends('layouts.admin')

@section('title', 'Cài đặt thanh toán')

@section('content')
<div class="admin-page-heading">
    <div>
        <span class="admin-page-eyebrow">THANH TOÁN</span>
        <h1>Cài đặt thanh toán ngân hàng</h1>
        <p>Cung cấp thông tin tài khoản ngân hàng để khách hàng có thể thanh toán bằng chuyển khoản QR.</p>
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

<form class="payment-settings-form" method="POST" action="{{ route('admin.payment-settings.update') }}">
    @csrf
    @method('PUT')

    <!-- Bank Account Section -->
    <section class="admin-form-panel payment-section">
        <div class="admin-form-panel-header">
            <div class="admin-form-panel-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
            <div class="admin-form-panel-header-text">
                <h2>Thông tin tài khoản ngân hàng</h2>
                <p>Điền đầy đủ thông tin bên dưới để kích hoạt thanh toán chuyển khoản.</p>
            </div>
        </div>

        <div class="admin-form-section">
            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label for="bank_code">Mã ngân hàng <span class="admin-required">*</span></label>
                    <input type="text" id="bank_code" name="bank_code" value="{{ old('bank_code', $setting->bank_code ?? '') }}" placeholder="VD: VCB, BIDV, ACB" required maxlength="20">
                    <small>Mã viết tắt của ngân hàng (ví dụ: VCB cho Vietcombank).</small>
                </div>
                <div class="admin-form-group">
                    <label for="bank_name">Tên ngân hàng <span class="admin-required">*</span></label>
                    <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name', $setting->bank_name ?? '') }}" placeholder="VD: Vietcombank" required maxlength="120">
                    <small>Tên đầy đủ của ngân hàng.</small>
                </div>
            </div>

            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label for="account_number">Số tài khoản <span class="admin-required">*</span></label>
                    <input type="text" id="account_number" name="account_number" value="{{ old('account_number', $setting->account_number ?? '') }}" placeholder="VD: 1234567890" required maxlength="50">
                    <small>Số tài khoản nhận tiền thanh toán.</small>
                </div>
                <div class="admin-form-group">
                    <label for="account_name">Tên tài khoản <span class="admin-required">*</span></label>
                    <input type="text" id="account_name" name="account_name" value="{{ old('account_name', $setting->account_name ?? '') }}" placeholder="VD: NGUYEN VAN A" required maxlength="120">
                    <small>Tên chủ tài khoản (viết hoa không dấu).</small>
                </div>
            </div>

            <div class="admin-form-row is-single">
                <div class="admin-form-group">
                    <label class="admin-checkbox-label">
                        <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $setting->is_enabled ?? false) ? 'checked' : '' }}>
                        <span>Kích hoạt thanh toán chuyển khoản ngân hàng</span>
                    </label>
                    <small>Khi bật, khách hàng sẽ thấy tùy chọn thanh toán bằng chuyển khoản QR trong quá trình đặt hàng.</small>
                </div>
            </div>
        </div>
    </section>

    <!-- Submit Actions -->
    <div class="payment-settings-actions">
        <button type="submit" class="admin-btn-primary admin-btn-lg">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Lưu tất cả thay đổi
        </button>
    </div>
</form>
@endsection

@push('styles')
<style>
.payment-settings-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.payment-section .admin-form-panel-header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 20px 24px;
    border-bottom: 1px solid #e8f0f5;
}

.payment-section .admin-form-panel-header-icon {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    background: linear-gradient(135deg, #438fc3 0%, #2d6a9f 100%);
    color: #fff;
}

.payment-section .admin-form-panel-header-text h2 {
    margin: 0 0 4px;
    font-size: 17px;
    font-weight: 700;
    color: #25445d;
}

.payment-section .admin-form-panel-header-text p {
    margin: 0;
    font-size: 13px;
    color: #6b8195;
}

/* Submit Actions */
.payment-settings-actions {
    display: flex;
    justify-content: flex-end;
    padding: 20px 0;
}

.admin-btn-lg {
    padding: 14px 28px;
    font-size: 15px;
}

/* Admin Buttons */
.admin-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border: 1.5px solid #dce8f0;
    border-radius: 10px;
    background: #fff;
    color: #466379;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s ease;
    text-decoration: none;
}

.admin-btn-secondary:hover {
    border-color: #438fc3;
    color: #438fc3;
    background: #f0f7fd;
    transform: translateY(-1px);
}

.admin-btn-danger {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border: 1.5px solid #f0d6d6;
    border-radius: 10px;
    background: #fff;
    color: #a44e4e;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s ease;
    text-decoration: none;
}

.admin-btn-danger:hover {
    border-color: #d24c4c;
    color: #fff;
    background: linear-gradient(135deg, #d24c4c 0%, #c43c3c 100%);
    transform: translateY(-1px);
}

.hidden {
    display: none !important;
}
</style>
@endpush
