@extends('layouts.store')

@section('title', 'Liên hệ')
@section('description', $pageContent['intro_description'])
@section('content')
    <section class="page-intro contact-intro">
        <div class="container">
            <span class="eyebrow">{{ $pageContent['intro_eyebrow'] }}</span>
            <h1>{{ $pageContent['intro_title_first'] }}<br><em>{{ $pageContent['intro_title_second'] }}</em></h1>
            <p>{{ $pageContent['intro_description'] }}</p>
            <div class="breadcrumb"><a href="{{ route('home') }}">Trang chủ</a><span>/</span><span>Liên hệ</span></div>
        </div>
    </section>
    <section class="container contact-section">
        <div class="contact-info">
            <span class="eyebrow">{{ $pageContent['info_eyebrow'] }}</span>
            <h2>{{ $pageContent['info_title_first'] }}<br><em>{{ $pageContent['info_title_second'] }}</em></h2>
            <p>{{ $pageContent['info_description'] }}</p>
            <a class="contact-chat-cta" href="{{ auth()->check() ? route('chat.index') : route('login') }}">
                {{ auth()->check() ? 'Trò chuyện trực tiếp với admin' : 'Đăng nhập để trò chuyện với admin' }}
                <span aria-hidden="true">↗</span>
            </a>
            <div class="contact-detail"><span class="contact-icon" aria-hidden="true">↗</span><div><span>{{ $pageContent['info_phone_label'] }}</span><a href="tel:{{ preg_replace('/\s+/', '', $pageContent['info_phone']) }}">{{ $pageContent['info_phone'] }}</a></div></div>
            <div class="contact-detail"><span class="contact-icon" aria-hidden="true">✉</span><div><span>{{ $pageContent['info_email_label'] }}</span><a href="mailto:{{ $pageContent['info_email'] }}">{{ $pageContent['info_email'] }}</a></div></div>
            <div class="contact-detail"><span class="contact-icon" aria-hidden="true">⌖</span><div><span>{{ $pageContent['info_address_label'] }}</span><span>{{ $pageContent['info_address'] }}</span></div></div>
            <div class="contact-hours"><span>{{ $pageContent['info_hours_label'] }}</span><p>{{ $pageContent['info_hours_weekday'] }} <strong>{{ $pageContent['info_hours_weekday_time'] }}</strong><br>{{ $pageContent['info_hours_sunday'] }} <strong>{{ $pageContent['info_hours_sunday_time'] }}</strong></p></div>
        </div>
        <div class="contact-form-wrap reveal">
            <h2>{{ $pageContent['form_title'] }}</h2>
            <p>{{ $pageContent['form_description'] }}</p>
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
                    <label>Chủ đề <input type="text" name="subject" value="{{ old('subject', request('subject')) }}" required maxlength="180" placeholder="Bạn cần chúng mình hỗ trợ gì?"></label>
                </div>
                <label>Lời nhắn <textarea name="message" rows="5" required maxlength="5000" placeholder="Chia sẻ với chúng mình nhé...">{{ old('message', request('message')) }}</textarea></label>
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
