@extends('layouts.store')

@section('title', 'Đã nhận đơn hàng')
@section('description', 'Cốc Mơ đã nhận được đơn đặt cốc custom của bạn.')
@section('content')
    <section class="custom-order-confirmation">
        <div class="custom-order-confirmation-card">
            <span class="custom-order-confirmation-icon" aria-hidden="true">✓</span>
            <span class="eyebrow">CẢM ƠN BẠN ĐÃ ĐẶT HÀNG</span>
            <h1>Cốc Mơ đã nhận<br><em>đơn hàng của bạn.</em></h1>
            <p>Mã đơn hàng của bạn là <strong>{{ $orderNumber }}</strong>. Tụi mình sẽ sớm liên hệ để xác nhận chi tiết và cách thanh toán.</p>
            @if ($previewUrl)
                <div class="custom-order-confirmation-preview">
                    <img src="{{ $previewUrl }}" alt="Cốc custom {{ $orderNumber }}" loading="lazy">
                </div>
            @endif
            <a class="button button-dark" href="{{ route('products.customize') }}">Tiếp tục thiết kế <span aria-hidden="true">↗</span></a>
        </div>
    </section>
@endsection
