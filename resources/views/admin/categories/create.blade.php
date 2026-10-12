@extends('layouts.admin')

@section('title', 'Tạo phân loại sản phẩm')
@section('heading', 'Tạo phân loại mới')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-category-form.css') }}?v={{ filemtime(public_path('css/admin-category-form.css')) }}">
@endpush

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">QUẢN LÝ KHO · PHÂN LOẠI</span>
            <h1>Tạo phân loại mới</h1>
            <p>Thêm một nhóm sản phẩm để đa dạng cửa hàng (ví dụ: <strong>Cốc sứ</strong>, <strong>Cốc giữ nhiệt</strong>, <strong>Phụ kiện</strong>…).</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="admin-btn-secondary">← Quay lại danh sách</a>
    </div>

    <section class="admin-form-panel">
        <div class="admin-form-panel-header">
            <div class="admin-form-panel-header-text">
                <h2>Thông tin phân loại</h2>
                <p>Điền tên, chọn biểu tượng và mô tả. Khách hàng sẽ thấy phân loại này ngay trên menu và trang chủ.</p>
            </div>
            <div class="admin-form-panel-icon" aria-hidden="true">🗂</div>
        </div>

        @include('admin.categories.partials._form', ['category' => null])
    </section>
@endsection
