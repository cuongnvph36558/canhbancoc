@extends('layouts.store')

@section('body-class', 'cup-homepage')
@section('title', 'Cốc in theo yêu cầu')
@section('description', 'Tạo chiếc cốc của riêng bạn tại Cốc Mơ. In ảnh, tên, lời nhắn và những khoảnh khắc bạn yêu thích lên cốc.')
@section('content')
    <div class="cup-home">
        <section class="cup-hero">
            <div class="cup-hero-copy">
                <span class="cup-eyebrow"><span>✳</span> STUDIO QUÀ TẶNG CÁ NHÂN HÓA</span>
                <h1>Một chiếc cốc<br>đậm chất <em>của bạn.</em></h1>
                <p>Ảnh bạn thương, câu chữ bạn chọn. Tụi mình biến những điều rất riêng thành chiếc cốc khiến mỗi ngày vui hơn một chút.</p>
                <div class="cup-hero-actions">
                    <a class="cup-button cup-button-primary" href="{{ route('products.index') }}">Khám phá bộ sưu tập <span aria-hidden="true">↗</span></a>
                    <a class="cup-button cup-button-soft" href="{{ route('contact') }}">Kể ý tưởng của bạn</a>
                </div>
                <div class="cup-hero-note"><span aria-hidden="true">✳</span><span>Thiết kế riêng · In chỉn chu · Gói quà xinh</span></div>
            </div>
            <div class="sky-cloudscape" aria-hidden="true">
                <span class="sky-cloud sky-cloud-one"></span>
                <span class="sky-cloud sky-cloud-two"></span>
                <span class="sky-cloud sky-cloud-three"></span>
            </div>
            <div class="cup-hero-art">
                <img src="{{ $products[0]['image'] }}" alt="Chiếc cốc Cốc Mơ được thiết kế theo yêu cầu">
                <span class="cup-art-label">MƠ STUDIO <span aria-hidden="true">✳</span> NO. 01</span>
                <div class="cup-hero-sticker"><span>YOUR IDEA</span><strong>♡</strong><span>YOUR CUP</span></div>
                <span class="cup-art-caption">MADE TO MAKE YOU SMILE</span>
            </div>
        </section>

        <section class="cup-benefits reveal" aria-label="Ưu điểm">
            <div><span aria-hidden="true">☁</span><p><strong>Thiết kế theo ý bạn</strong><small>Ảnh, tên hay lời nhắn đều được</small></p></div>
            <div><span aria-hidden="true">♡</span><p><strong>Gói quà thật xinh</strong><small>Trao tận tay, gửi tận thương</small></p></div>
            <div><span aria-hidden="true">✦</span><p><strong>In sắc nét, bền màu</strong><small>Cho chiếc cốc dùng thật lâu</small></p></div>
        </section>

        <section class="cup-featured reveal" id="featured-cups">
            <div class="cup-section-heading">
                <div>
                    <span class="cup-eyebrow">ĐƯỢC YÊU THÍCH NHẤT <span aria-hidden="true">✿</span></span>
                    <h2>Chọn một chiếc<br><em>thật giống bạn.</em></h2>
                </div>
                <a class="cup-view-all" href="{{ route('products.index') }}">Xem tất cả cốc <span aria-hidden="true">→</span></a>
            </div>
            <div class="cup-tools">
                <label class="cup-search">
                    <span aria-hidden="true">⌕</span>
                    <input id="cup-search" type="search" placeholder="Tìm chiếc cốc bạn thích..." aria-label="Tìm sản phẩm cốc">
                </label>
                <div class="cup-filters" aria-label="Lọc sản phẩm theo danh mục">
                    <button class="cup-filter is-selected" type="button" data-cup-filter="all" aria-pressed="true">Tất cả</button>
                    <button class="cup-filter" type="button" data-cup-filter="Cốc in hình" aria-pressed="false">In hình</button>
                    <button class="cup-filter" type="button" data-cup-filter="Cốc in tên" aria-pressed="false">In tên</button>
                    <button class="cup-filter" type="button" data-cup-filter="Cốc đôi" aria-pressed="false">Cốc đôi</button>
                    <button class="cup-filter" type="button" data-cup-filter="Quà tặng" aria-pressed="false">Quà tặng</button>
                </div>
            </div>
            <div class="product-grid cup-product-grid" id="cup-product-grid">
                @foreach ($products as $product)
                    @include('components.product-card', ['product' => $product])
                @endforeach
            </div>
            <p class="cup-no-results" id="cup-no-results" hidden>Chưa tìm thấy chiếc cốc phù hợp. Thử một từ khóa khác nhé!</p>
            <div class="cup-more"><a class="cup-button cup-button-soft" href="{{ route('products.index') }}">Khám phá thêm nhiều mẫu cốc <span aria-hidden="true">↓</span></a></div>
        </section>

        <div class="playful-divider" aria-hidden="true">
            <span>✦</span><span>♡</span><span>✿</span><span>☁</span><span>✦</span>
        </div>

        <section class="cup-categories reveal" aria-labelledby="cup-category-title">
            <div class="cup-section-heading">
                <div><span class="cup-eyebrow">CHỌN THEO TÂM TRẠNG</span><h2 id="cup-category-title">Hôm nay bạn muốn <em>món nào?</em></h2></div>
            </div>
            <div class="cup-category-grid">
                <a class="cup-category-card category-pink" href="{{ route('products.index') }}"><span class="category-emoji" aria-hidden="true">🐱</span><span class="category-label">Cốc in hình</span><span class="category-caption">Giữ boss bên cạnh</span><span class="category-arrow" aria-hidden="true">↗</span></a>
                <a class="cup-category-card category-lilac" href="{{ route('products.index') }}"><span class="category-emoji" aria-hidden="true">✍️</span><span class="category-label">Cốc tên riêng</span><span class="category-caption">Đúng tên, đúng gu</span><span class="category-arrow" aria-hidden="true">↗</span></a>
                <a class="cup-category-card category-peach" href="{{ route('products.index') }}"><span class="category-emoji" aria-hidden="true">💞</span><span class="category-label">Cốc đôi</span><span class="category-caption">Có đôi có cặp</span><span class="category-arrow" aria-hidden="true">↗</span></a>
                <a class="cup-category-card category-blue" href="{{ route('products.index') }}"><span class="category-emoji" aria-hidden="true">🎁</span><span class="category-label">Quà tặng</span><span class="category-caption">Gói ghém yêu thương</span><span class="category-arrow" aria-hidden="true">↗</span></a>
            </div>
        </section>

        <div class="playful-divider playful-divider-color" aria-hidden="true">
            <span>✿</span><span>✦</span><span>♡</span><span>✦</span><span>✿</span>
        </div>

        <section class="cup-custom-band reveal">
            <div class="cup-custom-copy">
                <span class="cup-eyebrow">KHÔNG CÓ CHIẾC NÀO GIỐNG CHIẾC NÀO</span>
                <h2>Ảnh của bạn,<br>chiếc cốc <em>của riêng bạn.</em></h2>
                <p>Chọn mẫu cốc, gửi hình ảnh hoặc lời nhắn yêu thích. Chúng mình sẽ cùng bạn hoàn thiện thiết kế trước khi in.</p>
                <a class="cup-button cup-button-primary" href="{{ route('contact') }}">Bắt đầu thiết kế <span aria-hidden="true">↗</span></a>
            </div>
            <div class="cup-custom-photo">
                <img src="https://images.unsplash.com/photo-1493857671505-72967e2e2760?auto=format&fit=crop&w=1000&q=85" alt="Món quà cốc sứ được chuẩn bị chỉn chu">
                <span>một chiếc cốc<br>mang tên bạn <span aria-hidden="true">♡</span></span>
            </div>
        </section>

        <section class="cup-love-cta reveal">
            <span class="cup-eyebrow">CỐC MƠ STUDIO · LÀM BẰNG CẢ SỰ DỄ THƯƠNG</span>
            <h2>Thêm chút xinh xắn<br>vào <em>ngày thường.</em></h2>
            <a class="cup-button cup-button-primary" href="{{ route('products.index') }}">Tìm chiếc cốc dành cho bạn <span aria-hidden="true">♡</span></a>
        </section>

    </div>
@endsection
