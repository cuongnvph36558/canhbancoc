@extends('layouts.store')

@section('body-class', 'cup-homepage')
@section('title', 'Cốc in theo yêu cầu')
@section('description', $pageContent['hero_description'])
@section('content')
    <div class="cup-home">
        <section class="cup-hero">
            <div class="cup-hero-copy">
                <span class="cup-eyebrow"><span>✳</span> {{ $pageContent['hero_eyebrow'] }}</span>
                <h1>{{ $pageContent['hero_title_first'] }}<br><em>{{ $pageContent['hero_title_second'] }}</em></h1>
                <p>{{ $pageContent['hero_description'] }}</p>
                <div class="cup-hero-actions">
                    <a class="cup-button cup-button-primary" href="{{ route('products.index') }}">{{ $pageContent['hero_primary_button'] }} <span aria-hidden="true">↗</span></a>
                    <a class="cup-button cup-button-soft" href="{{ route('contact') }}">{{ $pageContent['hero_secondary_button'] }}</a>
                </div>
                <div class="cup-hero-note"><span aria-hidden="true">✳</span><span>{{ $pageContent['hero_note'] }}</span></div>
            </div>
            <div class="sky-cloudscape" aria-hidden="true">
                <span class="sky-cloud sky-cloud-one"></span>
                <span class="sky-cloud sky-cloud-two"></span>
                <span class="sky-cloud sky-cloud-three"></span>
            </div>
            <div class="cup-hero-art">
                @if ($products[0]['image'])
                    <img src="{{ $products[0]['image'] }}" alt="Chiếc cốc Cốc Mơ được thiết kế theo yêu cầu">
                @endif
                <span class="cup-art-label">MƠ STUDIO <span aria-hidden="true">✳</span> NO. 01</span>
                <div class="cup-hero-sticker"><span>YOUR IDEA</span><strong>♡</strong><span>YOUR CUP</span></div>
                <span class="cup-art-caption">MADE TO MAKE YOU SMILE</span>
            </div>
        </section>

        <section class="cup-benefits reveal" aria-label="Ưu điểm">
            <div><span aria-hidden="true">☁</span><p><strong>{{ $pageContent['benefit_one_title'] }}</strong><small>{{ $pageContent['benefit_one_description'] }}</small></p></div>
            <div><span aria-hidden="true">♡</span><p><strong>{{ $pageContent['benefit_two_title'] }}</strong><small>{{ $pageContent['benefit_two_description'] }}</small></p></div>
            <div><span aria-hidden="true">✦</span><p><strong>{{ $pageContent['benefit_three_title'] }}</strong><small>{{ $pageContent['benefit_three_description'] }}</small></p></div>
        </section>

        <section class="cup-featured reveal" id="featured-cups">
            <div class="cup-section-heading">
                <div>
                    <span class="cup-eyebrow">{{ $pageContent['featured_eyebrow'] }} <span aria-hidden="true">✿</span></span>
                    <h2>{{ $pageContent['featured_title_first'] }}<br><em>{{ $pageContent['featured_title_second'] }}</em></h2>
                </div>
                <a class="cup-view-all" href="{{ route('products.index') }}">{{ $pageContent['featured_view_all'] }} <span aria-hidden="true">→</span></a>
            </div>
            <div class="cup-tools">
                <label class="cup-search">
                    <span aria-hidden="true">⌕</span>
                    <input id="cup-search" type="search" placeholder="{{ $pageContent['featured_search_placeholder'] }}" aria-label="{{ $pageContent['featured_search_placeholder'] }}">
                </label>
                <div class="cup-filters" aria-label="Lọc sản phẩm theo danh mục">
                    <button class="cup-filter is-selected" type="button" data-cup-filter="all" aria-pressed="true">{{ $pageContent['featured_filter_all'] }}</button>
                    <button class="cup-filter" type="button" data-cup-filter="Cốc in hình" aria-pressed="false">{{ $pageContent['featured_filter_image'] }}</button>
                    <button class="cup-filter" type="button" data-cup-filter="Cốc in tên" aria-pressed="false">{{ $pageContent['featured_filter_name'] }}</button>
                    <button class="cup-filter" type="button" data-cup-filter="Cốc đôi" aria-pressed="false">{{ $pageContent['featured_filter_pair'] }}</button>
                    <button class="cup-filter" type="button" data-cup-filter="Quà tặng" aria-pressed="false">{{ $pageContent['featured_filter_gift'] }}</button>
                </div>
            </div>
            <div class="product-grid cup-product-grid" id="cup-product-grid">
                @foreach ($products as $product)
                    @include('components.product-card', ['product' => $product])
                @endforeach
            </div>
            <p class="cup-no-results" id="cup-no-results" hidden>{{ $pageContent['featured_empty_message'] }}</p>
            <div class="cup-more"><a class="cup-button cup-button-soft" href="{{ route('products.index') }}">{{ $pageContent['featured_more_button'] }} <span aria-hidden="true">↓</span></a></div>
        </section>

        <div class="playful-divider" aria-hidden="true">
            <span>✦</span><span>♡</span><span>✿</span><span>☁</span><span>✦</span>
        </div>

        <section class="cup-categories reveal" aria-labelledby="cup-category-title">
            <div class="cup-section-heading">
                <div><span class="cup-eyebrow">{{ $pageContent['categories_eyebrow'] }}</span><h2 id="cup-category-title">{{ $pageContent['categories_title_first'] }} <em>{{ $pageContent['categories_title_second'] }}</em></h2></div>
            </div>
            <div class="cup-category-grid">
                <a class="cup-category-card category-pink" href="{{ route('products.index') }}"><span class="category-emoji" aria-hidden="true">🐱</span><span class="category-label">{{ $pageContent['category_one_label'] }}</span><span class="category-caption">{{ $pageContent['category_one_caption'] }}</span><span class="category-arrow" aria-hidden="true">↗</span></a>
                <a class="cup-category-card category-lilac" href="{{ route('products.index') }}"><span class="category-emoji" aria-hidden="true">✍️</span><span class="category-label">{{ $pageContent['category_two_label'] }}</span><span class="category-caption">{{ $pageContent['category_two_caption'] }}</span><span class="category-arrow" aria-hidden="true">↗</span></a>
                <a class="cup-category-card category-peach" href="{{ route('products.index') }}"><span class="category-emoji" aria-hidden="true">💞</span><span class="category-label">{{ $pageContent['category_three_label'] }}</span><span class="category-caption">{{ $pageContent['category_three_caption'] }}</span><span class="category-arrow" aria-hidden="true">↗</span></a>
                <a class="cup-category-card category-blue" href="{{ route('products.index') }}"><span class="category-emoji" aria-hidden="true">🎁</span><span class="category-label">{{ $pageContent['category_four_label'] }}</span><span class="category-caption">{{ $pageContent['category_four_caption'] }}</span><span class="category-arrow" aria-hidden="true">↗</span></a>
            </div>
        </section>

        <div class="playful-divider playful-divider-color" aria-hidden="true">
            <span>✿</span><span>✦</span><span>♡</span><span>✦</span><span>✿</span>
        </div>

        <section class="cup-custom-band reveal">
            <div class="cup-custom-copy">
                <span class="cup-eyebrow">{{ $pageContent['custom_eyebrow'] }}</span>
                <h2>{{ $pageContent['custom_title_first'] }}<br><em>{{ $pageContent['custom_title_second'] }}</em></h2>
                <p>{{ $pageContent['custom_description'] }}</p>
                <a class="cup-button cup-button-primary" href="{{ route('contact') }}">{{ $pageContent['custom_button'] }} <span aria-hidden="true">↗</span></a>
            </div>
            <div class="cup-custom-photo">
                @if ($pageContent['custom_image_url'])
                    <img src="{{ $pageContent['custom_image_url'] }}" alt="{{ $pageContent['custom_image_alt'] }}">
                @endif
                <span>{{ $pageContent['custom_image_caption_first'] }}<br>{{ $pageContent['custom_image_caption_second'] }} <span aria-hidden="true">♡</span></span>
            </div>
        </section>

        <section class="cup-love-cta reveal">
            <span class="cup-eyebrow">{{ $pageContent['closing_eyebrow'] }}</span>
            <h2>{{ $pageContent['closing_title_first'] }}<br><em>{{ $pageContent['closing_title_second'] }}</em></h2>
            <a class="cup-button cup-button-primary" href="{{ route('products.index') }}">{{ $pageContent['closing_button'] }} <span aria-hidden="true">♡</span></a>
        </section>

    </div>
@endsection
