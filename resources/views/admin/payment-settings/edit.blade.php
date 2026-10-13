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

<form class="payment-settings-form" method="POST" action="{{ route('admin.payment-settings.update') }}" enctype="multipart/form-data">
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

    <!-- QR Code Section -->
    <section class="admin-form-panel payment-section">
        <div class="admin-form-panel-header">
            <div class="admin-form-panel-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            </div>
            <div class="admin-form-panel-header-text">
                <h2>Mã QR thanh toán</h2>
                <p>Tải lên hình ảnh mã QR VietQR để khách hàng có thể quét và thanh toán nhanh chóng.</p>
            </div>
        </div>

        <div class="admin-form-section">
            <div class="qr-upload-container">
                @if($setting && $setting->qr_image_path)
                    <div class="qr-image-preview">
                        <div class="qr-image-wrapper">
                            <img src="{{ Storage::url($setting->qr_image_path) }}" alt="Mã QR thanh toán" id="qr-preview-img">
                            <div class="qr-image-badge">QR hiện tại</div>
                        </div>
                        <div class="qr-upload-actions">
                            <label for="qr_image" class="admin-btn-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                Thay đổi ảnh
                            </label>
                            <a href="{{ route('admin.payment-settings.delete-qr-image') }}" class="admin-btn-danger" onclick="return confirm('Bạn có chắc muốn xóa mã QR này?')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                Xóa ảnh
                            </a>
                        </div>
                        <input type="file" id="qr_image" name="qr_image" accept="image/png,image/jpeg,image/jpg" class="hidden">
                    </div>
                @else
                    <div class="qr-upload-area" id="qr-upload-area">
                        <label for="qr_image" class="qr-dropzone">
                            <div class="qr-dropzone-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                            <span class="qr-dropzone-text">Kéo thả hình ảnh mã QR vào đây</span>
                            <span class="qr-dropzone-hint">hoặc nhấn để chọn file (PNG, JPG, JPEG - tối đa 2MB)</span>
                            <input type="file" id="qr_image" name="qr_image" accept="image/png,image/jpeg,image/jpg" class="hidden">
                        </label>
                    </div>
                    <div class="qr-preview-area hidden" id="qr-preview-area">
                        <div class="qr-image-preview">
                            <div class="qr-image-wrapper">
                                <img src="" alt="Preview" id="qr-new-preview">
                                <div class="qr-image-badge">Ảnh mới</div>
                            </div>
                            <div class="qr-upload-actions">
                                <label for="qr_image_new" class="admin-btn-secondary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    Chọn lại
                                </label>
                                <button type="button" class="admin-btn-danger" id="qr-remove-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    Xóa
                                </button>
                            </div>
                        </div>
                        <input type="file" id="qr_image_new" name="qr_image" accept="image/png,image/jpeg,image/jpg" class="hidden">
                    </div>
                @endif
            </div>
        </div>

        <div class="qr-guide-box">
            <div class="qr-guide-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            </div>
            <div class="qr-guide-text">
                <strong>Hướng dẫn tạo mã QR VietQR:</strong>
                <ul>
                    <li>Đăng nhập vào ứng dụng ngân hàng của bạn</li>
                    <li>Tìm chức năng "Quét mã QR" hoặc "Chia sẻ mã QR"</li>
                    <li>Tải xuống hình ảnh mã QR với thông tin tài khoản</li>
                    <li>Upload hình ảnh đó lên đây</li>
                </ul>
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

/* QR Upload Styles */
.qr-upload-container {
    padding: 0;
}

.qr-dropzone {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 14px;
    padding: 50px 24px;
    margin: 0 24px;
    border: 2px dashed #c5dce8;
    border-radius: 16px;
    background: linear-gradient(145deg, #f8fcff 0%, #f0f7fc 100%);
    cursor: pointer;
    transition: all .25s ease;
}

.qr-dropzone:hover {
    border-color: #438fc3;
    background: linear-gradient(145deg, #f0f7fd 0%, #e8f4fc 100%);
}

.qr-dropzone-icon {
    width: 80px;
    height: 80px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: linear-gradient(135deg, #e8f4fc 0%, #d9edf8 100%);
    color: #438fc3;
}

.qr-dropzone-text {
    color: #25445d;
    font-size: 16px;
    font-weight: 600;
}

.qr-dropzone-hint {
    color: #6b8195;
    font-size: 13px;
}

/* QR Preview */
.qr-image-preview {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    padding: 24px;
}

.qr-image-wrapper {
    position: relative;
    display: inline-block;
}

.qr-image-wrapper img {
    max-width: 300px;
    max-height: 300px;
    width: 100%;
    object-fit: contain;
    border-radius: 16px;
    border: 3px solid #e8f0f5;
    background: #fff;
    box-shadow: 0 4px 20px rgba(67, 143, 195, 0.1);
}

.qr-image-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 6px 14px;
    border-radius: 20px;
    background: linear-gradient(135deg, #438fc3 0%, #2d6a9f 100%);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(67, 143, 195, 0.3);
}

.qr-upload-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: center;
}

/* QR Guide Box */
.qr-guide-box {
    display: flex;
    gap: 16px;
    margin: 0 24px 24px;
    padding: 18px 20px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f0f7fd 0%, #e8f4fc 100%);
    border: 1px solid #d0e8f5;
}

.qr-guide-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    display: grid;
    place-items: center;
    border-radius: 10px;
    background: linear-gradient(135deg, #438fc3 0%, #2d6a9f 100%);
    color: #fff;
}

.qr-guide-text {
    flex: 1;
}

.qr-guide-text strong {
    display: block;
    margin-bottom: 8px;
    color: #25445d;
    font-size: 13px;
    font-weight: 700;
}

.qr-guide-text ul {
    margin: 0;
    padding-left: 20px;
    color: #466379;
    font-size: 12px;
    line-height: 1.9;
}

.qr-guide-text li {
    margin: 0;
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('qr_image');
    const fileInputNew = document.getElementById('qr_image_new');
    const previewArea = document.getElementById('qr-preview-area');
    const uploadArea = document.getElementById('qr-upload-area');
    const previewImg = document.getElementById('qr-new-preview');
    const removeBtn = document.getElementById('qr-remove-btn');

    const triggerFileInput = fileInput || fileInputNew;
    
    if (triggerFileInput) {
        triggerFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    if (previewImg) {
                        previewImg.src = event.target.result;
                    }
                    if (uploadArea) uploadArea.classList.add('hidden');
                    if (previewArea) previewArea.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            if (fileInputNew) fileInputNew.value = '';
            if (previewArea) previewArea.classList.add('hidden');
            if (uploadArea) uploadArea.classList.remove('hidden');
        });
    }

    // Drag and drop
    const dropzone = document.querySelector('.qr-dropzone');
    if (dropzone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzone.style.borderColor = '#438fc3';
                dropzone.style.background = 'linear-gradient(145deg, #f0f7fd 0%, #e8f4fc 100%)';
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzone.style.borderColor = '#c5dce8';
                dropzone.style.background = 'linear-gradient(145deg, #f8fcff 0%, #f0f7fc 100%)';
            });
        });

        dropzone.addEventListener('drop', function(e) {
            const files = e.dataTransfer.files;
            if (files.length > 0 && triggerFileInput) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(files[0]);
                triggerFileInput.files = dataTransfer.files;
                triggerFileInput.dispatchEvent(new Event('change'));
            }
        });
    }
});
</script>
@endpush
