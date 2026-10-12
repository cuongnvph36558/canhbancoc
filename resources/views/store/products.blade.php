@extends('layouts.store')

@section('title', $currentCategory ? 'Phân loại: '.$currentCategory->name : 'Sản phẩm')
@section('description', $currentCategory
    ? 'Khám phá các sản phẩm '.($currentCategory->icon ? $currentCategory->icon.' ' : '').$currentCategory->name.' tại Cốc Mơ.'
    : 'Khám phá những chiếc cốc in theo yêu cầu, được thiết kế riêng để lưu giữ khoảnh khắc và gửi tặng người thương.')
@section('content')
    <section class="page-intro">
        <div class="container">
            <span class="eyebrow">BỘ SƯU TẬP CỐC MƠ</span>
            @if ($currentCategory)
                <h1>{!! $currentCategory->icon ? $currentCategory->icon.' ' : '' !!}{{ $currentCategory->name }}<br><em>{{ $currentCategory->description ?: 'Những chiếc cốc xinh thuộc dòng '.$currentCategory->name.'.' }}</em></h1>
                <p>{{ count($products) }} mẫu cốc thuộc dòng {{ $currentCategory->name }} đang chờ bạn.</p>
            @else
                <h1>Một chiếc cốc<br><em>mang câu chuyện của bạn.</em></h1>
                <p>Chọn mẫu cốc, gửi hình và tên bạn yêu. Chúng mình sẽ biến ý tưởng riêng thành món quà xinh xắc mỗi ngày.</p>
            @endif
            <a class="button button-dark" href="{{ route('products.customize') }}">Tự thiết kế cốc <span aria-hidden="true">↗</span></a>
            <div class="breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a>
                <span>/</span>
                @if ($currentCategory)
                    <a href="{{ route('products.index') }}">Sản phẩm</a>
                    <span>/</span>
                    <span>{{ $currentCategory->name }}</span>
                @else
                    <span>Sản phẩm</span>
                @endif
            </div>
        </div>
    </section>

    @if (($categories ?? collect())->isNotEmpty())
        <section class="collection-section collection-filters" aria-label="Phân loại sản phẩm">
            <div class="container category-chips">
                <a @class(['category-chip', 'is-active' => $currentCategory === null]) href="{{ route('products.index') }}">Tất cả</a>
                @foreach ($categories as $category)
                    <a @class(['category-chip', 'is-active' => optional($currentCategory)->id === $category->id]) href="{{ route('products.category', $category->slug) }}">
                        {{ $category->icon ? $category->icon.' ' : '' }}{{ $category->name }}
                        <small>{{ $category->products_count ?? 0 }}</small>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="container collection-section reveal">
        <div class="collection-toolbar">
            <span>{{ count($products) }} {{ $currentCategory ? 'mẫu '.strtolower($currentCategory->name) : 'mẫu cốc' }} đang chờ bạn</span>
            <span>In theo ý bạn · Gói quà thật xinh <span aria-hidden="true">✳</span></span>
        </div>
        <div class="product-grid">
            @forelse ($products as $product)
                @include('components.product-card', ['product' => $product])
            @empty
                <div class="collection-empty">
                    <p>{{ $currentCategory ? 'Phân loại này hiện chưa có sản phẩm nào. Hãy quay lại ' : '' }}<a href="{{ route('products.index') }}">{{ $currentCategory ? 'trang sản phẩm' : '' }}</a>{{ $currentCategory ? ' để xem các dòng khác.' : '' }}</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection

@once
    @push('styles')
        <style>
            .category-chips {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                padding-top: 24px;
                padding-bottom: 8px;
            }

            .category-chip {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 16px;
                border-radius: 999px;
                border: 1px solid #e0d6cb;
                background: #fff;
                color: #5d4534;
                font-size: 14px;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.2s ease;
            }

            .category-chip small {
                background: #f3ecde;
                color: #876b4e;
                padding: 2px 8px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 700;
            }

            .category-chip:hover {
                border-color: #5d4534;
                transform: translateY(-1px);
            }

            .category-chip.is-active {
                background: #5d4534;
                color: #fff;
                border-color: #5d4534;
            }

            .category-chip.is-active small {
                background: rgba(255, 255, 255, 0.18);
                color: #fff;
            }

            .collection-empty {
                grid-column: 1 / -1;
                padding: 60px 20px;
                text-align: center;
                background: #faf5ee;
                border-radius: 16px;
                color: #876b4e;
            }

            .collection-empty a {
                color: #5d4534;
                font-weight: 600;
            }
        </style>
    @endpush
@endonce
