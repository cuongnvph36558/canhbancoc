@extends('layouts.store')

@section('title', 'Giới thiệu')
@section('description', $pageContent['intro_description'])
@section('content')
    <section class="page-intro about-intro">
        <div class="container">
            <span class="eyebrow">{{ $pageContent['intro_eyebrow'] }}</span>
            <h1>{{ $pageContent['intro_title_first'] }}<br><em>{{ $pageContent['intro_title_second'] }}</em></h1>
            <p>{{ $pageContent['intro_description'] }}</p>
            <div class="breadcrumb"><a href="{{ route('home') }}">Trang chủ</a><span>/</span><span>Giới thiệu</span></div>
        </div>
    </section>
    <section class="container about-feature reveal">
        <div class="about-feature-image">
            @if ($pageContent['feature_image_url'])
                <img src="{{ $pageContent['feature_image_url'] }}" alt="{{ $pageContent['feature_image_alt'] }}">
            @endif
        </div>
        <div class="about-feature-copy">
            <span class="eyebrow">{{ $pageContent['feature_eyebrow'] }}</span>
            <h2>{{ $pageContent['feature_title_first'] }}<br><em>{{ $pageContent['feature_title_second'] }}</em></h2>
            <p>{{ $pageContent['feature_paragraph_one'] }}</p>
            <p>{{ $pageContent['feature_paragraph_two'] }}</p>
        </div>
    </section>
    <section class="about-values reveal">
        <div class="container">
            <div class="section-heading"><div><span class="eyebrow">{{ $pageContent['values_eyebrow'] }}</span><h2>{{ $pageContent['values_title_first'] }} <em>{{ $pageContent['values_title_second'] }}</em></h2></div></div>
            <div class="about-values-grid">
            <article><span>01</span><h3>{{ $pageContent['value_one_title'] }}</h3><p>{{ $pageContent['value_one_description'] }}</p></article>
            <article><span>02</span><h3>{{ $pageContent['value_two_title'] }}</h3><p>{{ $pageContent['value_two_description'] }}</p></article>
            <article><span>03</span><h3>{{ $pageContent['value_three_title'] }}</h3><p>{{ $pageContent['value_three_description'] }}</p></article>
            </div>
        </div>
    </section>
    <section class="closing-note container reveal">
        <span class="eyebrow">{{ $pageContent['closing_eyebrow'] }}</span>
        <h2>{{ $pageContent['closing_title_first'] }}<br><em>{{ $pageContent['closing_title_second'] }}</em></h2>
        <a class="button button-dark" href="{{ route('products.index') }}">{{ $pageContent['closing_button'] }} <span aria-hidden="true">↗</span></a>
    </section>
@endsection
