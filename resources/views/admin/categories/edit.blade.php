@extends('layouts.admin')

@section('title', 'Chỉnh sửa phân loại')
@section('heading', 'Chỉnh sửa phân loại')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-category-form.css') }}?v={{ filemtime(public_path('css/admin-category-form.css')) }}">
@endpush

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">QUẢN LÝ KHO · PHÂN LOẠI</span>
            <h1>Chỉnh sửa phân loại</h1>
            <p>
                Đang cập nhật: <strong>{{ $category->icon ? $category->icon.' ' : '' }}{{ $category->name }}</strong>
                <span style="color:#94a6b6; font-size:12px;">·</span>
                <span style="color:#94a6b6; font-size:12px;">{{ $category->products_count ?? 0 }} sản phẩm đang dùng</span>
            </p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="admin-btn-secondary">← Quay lại danh sách</a>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success" role="status">{{ session('success') }}</div>
    @endif

    <section class="admin-form-panel">
        <div class="admin-form-panel-header">
            <div class="admin-form-panel-header-text">
                <h2>Thông tin phân loại</h2>
                <p>Cập nhật nội dung cho <strong>{{ $category->name }}</strong>. Mọi thay đổi sẽ áp dụng ngay cho toàn bộ sản phẩm thuộc phân loại.</p>
            </div>
            <div class="admin-form-panel-icon" aria-hidden="true">{{ $category->icon ?: '🗂' }}</div>
        </div>

        @include('admin.categories.partials._form', ['category' => $category])
    </section>
@endsection
