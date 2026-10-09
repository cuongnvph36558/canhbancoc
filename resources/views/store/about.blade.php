@extends('layouts.store')

@section('title', 'Giới thiệu')
@section('description', 'Câu chuyện Cốc Mơ Studio — nơi những chiếc cốc cá nhân hóa được tạo nên từ ý tưởng và khoảnh khắc bạn yêu.')
@section('content')
    <section class="page-intro about-intro">
        <div class="container">
            <span class="eyebrow">CÂU CHUYỆN CỐC MƠ STUDIO</span>
            <h1>Một chút yêu thương<br><em>in lên chiếc cốc nhỏ.</em></h1>
            <p>Cốc Mơ bắt đầu từ một ý tưởng giản dị: biến những hình ảnh, cái tên và kỷ niệm bạn yêu thành món quà có thể chạm vào mỗi ngày.</p>
            <div class="breadcrumb"><a href="{{ route('home') }}">Trang chủ</a><span>/</span><span>Giới thiệu</span></div>
        </div>
    </section>
    <section class="container about-feature reveal">
        <div class="about-feature-image"><img src="https://images.unsplash.com/photo-1493857671505-72967e2e2760?auto=format&fit=crop&w=1300&q=85" alt="Chiếc cốc được chuẩn bị thành món quà đáng yêu"></div>
        <div class="about-feature-copy">
            <span class="eyebrow">MỖI CHIẾC CỐC MANG MỘT DẤU ẤN</span>
            <h2>Ý tưởng của bạn,<br><em>chúng mình chăm chút.</em></h2>
            <p>Một tấm hình thân quen, cái tên dễ thương hay câu nói chỉ hai người hiểu — đôi khi những điều nhỏ xíu lại trở thành món quà đáng nhớ nhất.</p>
            <p>Cốc Mơ cùng bạn chọn mẫu, sắp xếp thiết kế và hoàn thiện từng chiếc cốc theo yêu cầu. Tụi mình chăm chút cả màu in lẫn cách gói để món quà khi đến tay người thương thật trọn vẹn.</p>
        </div>
    </section>
    <section class="about-values reveal">
        <div class="container">
            <div class="section-heading"><div><span class="eyebrow">ĐIỀU CỐC MƠ LUÔN GÌN GIỮ</span><h2>Gói ghém thật xinh, <em>trao gửi thật tình.</em></h2></div></div>
            <div class="about-values-grid">
                <article><span>01</span><h3>Thiết kế theo câu chuyện của bạn</h3><p>Mỗi hình in, tên gọi hay lời nhắn đều được chọn riêng cho người nhận.</p></article>
                <article><span>02</span><h3>Chăm chút từng chi tiết nhỏ</h3><p>Tụi mình cùng bạn xem lại bố cục để chiếc cốc xinh từ mọi góc nhìn.</p></article>
                <article><span>03</span><h3>Gói quà bằng cả sự dễ thương</h3><p>Mỗi đơn hàng được chuẩn bị chỉn chu, sẵn sàng trao gửi yêu thương.</p></article>
            </div>
        </div>
    </section>
    <section class="closing-note container reveal">
        <span class="eyebrow">CỐC MƠ STUDIO · MADE JUST FOR YOU</span>
        <h2>Chọn một chiếc cốc,<br>gửi một niềm vui <em>thật riêng.</em></h2>
        <a class="button button-dark" href="{{ route('products.index') }}">Chọn mẫu cốc <span aria-hidden="true">↗</span></a>
    </section>
@endsection
