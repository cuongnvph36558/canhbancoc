@extends('layouts.admin')

@section('title', 'Chi tiết lời nhắn')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">HỘP THƯ LIÊN HỆ</span>
            <h1>{{ $inquiry->subject }}</h1>
            <p>Gửi lúc {{ $inquiry->created_at->format('H:i d/m/Y') }}</p>
        </div>
        <a class="admin-action-button" href="{{ route('admin.contact-inquiries.index') }}">← Quay lại hộp thư</a>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success" role="status">{{ session('success') }}</div>
    @endif

    <section class="admin-products-panel inquiry-detail" aria-label="Thông tin lời nhắn">
        <div class="inquiry-contact">
            <div><span>HỌ VÀ TÊN</span><strong>{{ $inquiry->name }}</strong></div>
            <div><span>EMAIL</span><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></div>
            <div><span>SỐ ĐIỆN THOẠI</span>
                @if ($inquiry->phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $inquiry->phone) }}">{{ $inquiry->phone }}</a>
                @else
                    <strong>Chưa cung cấp</strong>
                @endif
            </div>
        </div>

        <div class="inquiry-message">
            <h2>Nội dung lời nhắn</h2>
            <p>{{ $inquiry->message }}</p>
        </div>

        <form class="inquiry-status-form" method="POST" action="{{ route('admin.contact-inquiries.update-status', $inquiry) }}">
            @csrf
            @method('PATCH')
            <label for="inquiry-status">Trạng thái xử lý</label>
            <select id="inquiry-status" name="status">
                @foreach ($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected($inquiry->status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')
                <span class="chat-form-error" role="alert">{{ $message }}</span>
            @enderror
            <button class="admin-action-button" type="submit">Cập nhật trạng thái</button>
            <a class="admin-action-button" href="mailto:{{ $inquiry->email }}?subject={{ rawurlencode('Phản hồi: '.$inquiry->subject) }}">Trả lời qua email</a>
        </form>
    </section>
@endsection
