@extends('layouts.store')

@section('title', 'Sản phẩm')
@section('description', 'Khám phá những chiếc cốc in theo yêu cầu, được thiết kế riêng để lưu giữ khoảnh khắc và gửi tặng người thương.')
@section('content')
    <section class="page-intro">
        <div class="container">
            <span class="eyebrow">BỘ SƯU TẬP CỐC MƠ</span>
            <h1>Một chiếc cốc<br><em>mang câu chuyện của bạn.</em></h1>
            <p>Chọn mẫu cốc, gửi hình và tên bạn yêu. Chúng mình sẽ biến ý tưởng riêng thành món quà xinh xắn mỗi ngày.</p>
            <a class="button button-dark" href="{{ route('products.customize') }}">Tự thiết kế cốc <span aria-hidden="true">↗</span></a>
            <div class="breadcrumb"><a href="{{ route('home') }}">Trang chủ</a><span>/</span><span>Sản phẩm</span></div>
        </div>
    </section>
    <section class="container collection-section reveal">
        <div class="collection-toolbar"><span>{{ count($products) }} mẫu cốc đang chờ bạn</span><span>In theo ý bạn · Gói quà thật xinh <span aria-hidden="true">✳</span></span></div>
        <div class="product-grid">
            @foreach ($products as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>
@endsection
