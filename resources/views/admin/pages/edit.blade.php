@extends('layouts.admin')

@section('title', 'Nội dung '.$definition['title'])

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">NỘI DUNG WEBSITE</span>
            <h1>Chỉnh sửa trang {{ $definition['title'] }}</h1>
            <p>{{ $definition['description'] }}</p>
        </div>
        <a href="{{ $page === 'home' ? route('home') : route('about') }}" class="admin-btn-secondary" target="_blank" rel="noopener">
            Xem trang <span aria-hidden="true">↗</span>
        </a>
    </div>

    <nav class="admin-page-tabs" aria-label="Chọn trang cần chỉnh sửa">
        <a @class(['admin-page-tab', 'is-active' => $page === 'home']) href="{{ route('admin.pages.edit', 'home') }}">Trang chủ</a>
        <a @class(['admin-page-tab', 'is-active' => $page === 'about']) href="{{ route('admin.pages.edit', 'about') }}">Giới thiệu</a>
        <a @class(['admin-page-tab', 'is-active' => $page === 'header_footer']) href="{{ route('admin.pages.edit', 'header_footer') }}">Header & Footer</a>
    </nav>

    @if (session('success'))
        <div class="admin-alert admin-alert-success" role="status">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="admin-alert admin-alert-error" role="alert">Vui lòng kiểm tra lại các nội dung được đánh dấu.</div>
    @endif

    <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="admin-content-editor" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @foreach ($definition['sections'] as $section)
            <section class="admin-content-section">
                <h2>{{ $section['title'] }}</h2>
                <div class="admin-content-fields">
                    @foreach ($section['fields'] as $key => $field)
                        <div class="admin-content-field">
                            <label for="content_{{ $key }}">{{ $field['label'] }}</label>
                            @if (($field['type'] ?? 'text') === 'textarea')
                                <textarea id="content_{{ $key }}" name="content[{{ $key }}]" rows="4" maxlength="{{ $field['max'] ?? 5000 }}" required>{{ old('content.'.$key, $content[$key]) }}</textarea>
                            @elseif (($field['type'] ?? 'text') === 'image')
                                <input
                                    id="content_{{ $key }}"
                                    name="content[{{ $key }}]"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                >
                                <small>Ảnh JPG, PNG hoặc WebP; dung lượng tối đa 5 MB. Để trống để giữ ảnh hiện tại.</small>
                                @if ($content[$key])
                                    <img class="admin-content-image-preview" src="{{ $content[$key] }}" alt="Ảnh hiện tại">
                                @endif
                            @else
                                <input
                                    id="content_{{ $key }}"
                                    name="content[{{ $key }}]"
                                    type="{{ $field['type'] ?? 'text' }}"
                                    value="{{ old('content.'.$key, $content[$key]) }}"
                                    maxlength="{{ $field['max'] ?? 5000 }}"
                                    required
                                >
                            @endif
                            @error('content.'.$key)
                                <small class="admin-content-error">{{ $message }}</small>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="admin-content-actions">
            <button type="submit" class="admin-btn-primary">Lưu nội dung</button>
        </div>
    </form>
@endsection
