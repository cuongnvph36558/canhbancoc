@extends('layouts.store')

@section('title', 'Liên hệ')
@section('description', 'Cần tư vấn mẫu cốc hoặc thiết kế riêng? Gửi lời nhắn cho Cốc Mơ Studio, tụi mình luôn sẵn lòng hỗ trợ.')
@section('content')
    <section class="page-intro contact-intro">
        <div class="container">
            <span class="eyebrow">CỐC MƠ LUÔN Ở ĐÂY</span>
            <h1>Ý tưởng của bạn,<br><em>tụi mình cùng làm.</em></h1>
            <p>Cần tư vấn mẫu cốc, gửi hình in hay nhờ gợi ý món quà? Nhắn cho tụi mình nhé, Cốc Mơ luôn sẵn lòng lắng nghe.</p>
            <div class="breadcrumb"><a href="{{ route('home') }}">Trang chủ</a><span>/</span><span>Liên hệ</span></div>
        </div>
    </section>
    <section class="container contact-section">
        <div class="contact-info">
            <span class="eyebrow">KẾT NỐI VỚI CỐC MƠ</span>
            <h2>Một lời chào<br><em>mở đầu chiếc cốc.</em></h2>
            <p>Đội ngũ nhỏ của chúng mình thường phản hồi trong một ngày làm việc.</p>
            <div class="contact-detail"><span class="contact-icon" aria-hidden="true">↗</span><div><span>GỌI CHO CHÚNG MÌNH</span><a href="tel:{{ preg_replace('/\s+/', '', config('store.phone')) }}">{{ config('store.phone') }}</a></div></div>
            <div class="contact-detail"><span class="contact-icon" aria-hidden="true">✉</span><div><span>GỬI EMAIL</span><a href="mailto:{{ config('store.email') }}">{{ config('store.email') }}</a></div></div>
            <div class="contact-detail"><span class="contact-icon" aria-hidden="true">⌖</span><div><span>GHÉ THĂM CỬA HÀNG</span><span>{{ config('store.address') }}</span></div></div>
            <div class="contact-hours"><span>GIỜ MỞ CỬA</span><p>Thứ Hai – Thứ Bảy <strong>09:00 – 20:00</strong><br>Chủ Nhật <strong>10:00 – 18:00</strong></p></div>
        </div>
        <div class="contact-form-wrap reveal">
            <h2>Gửi lời nhắn cho chúng mình</h2>
            <p>Cốc Mơ sẽ hồi âm bạn sớm nhất có thể.</p>
            @if (session('success'))
                <div class="form-success" role="status">{{ session('success') }}</div>
            @endif
            <form class="contact-form" method="POST" action="{{ route('contact.submit') }}">
                @csrf
                <div class="form-row">
                    <label>Họ và tên <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="120" placeholder="Tên của bạn"></label>
                    <label>Email <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required maxlength="255" placeholder="ban@email.com"></label>
                </div>
                <div class="form-row">
                    <label>Số điện thoại <span class="optional">(không bắt buộc)</span><input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" maxlength="30" placeholder="09xx xxx xxx"></label>
                    <label>Chủ đề <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="180" placeholder="Bạn cần chúng mình hỗ trợ gì?"></label>
                </div>
                <label>Lời nhắn <textarea name="message" rows="5" required maxlength="5000" placeholder="Chia sẻ với chúng mình nhé...">{{ old('message') }}</textarea></label>
                @if ($errors->any())
                    <div class="form-errors" role="alert">
                        <p>Vui lòng kiểm tra lại thông tin bạn vừa nhập.</p>
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                <button class="button button-dark" type="submit">Gửi lời nhắn <span aria-hidden="true">↗</span></button>
            </form>
        </div>
    </section>
@endsection
