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
            <div class="toolbar-left">
                <span class="product-count">{{ count($products) }} {{ $currentCategory ? 'mẫu '.strtolower($currentCategory->name) : 'mẫu cốc' }} đang chờ bạn</span>
            </div>
            <div class="toolbar-right">
                <span class="toolbar-tagline">In theo ý bạn · Gói quà thật xinh <span aria-hidden="true">✳</span></span>
                <div class="sort-wrapper">
                    <label for="sort-select" class="sort-label">Sắp xếp:</label>
                    <select id="sort-select" class="sort-select" onchange="window.location.href=this.value">
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ request('sort') == 'newest' || !request('sort') ? 'selected' : '' }}>Mới nhất</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá: Thấp → Cao</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá: Cao → Thấp</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'name']) }}" {{ request('sort') == 'name' ? 'selected' : '' }}>Tên A → Z</option>
                    </select>
                </div>
            </div>
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
            /* Category Filter Section */
            .collection-filters {
                background: var(--cream);
                padding-bottom: 0;
            }
            
            .category-chips {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                padding-top: 20px;
                padding-bottom: 24px;
            }

            .category-chip {
                position: relative;
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 10px 20px;
                border-radius: 50px;
                border: 2px solid #e0d6cb;
                background: #fff;
                color: #5d4534;
                font-size: 14px;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
                box-shadow: 0 2px 8px rgba(93, 69, 52, 0.06);
            }

            .category-chip:hover {
                border-color: #5d4534;
                transform: translateY(-3px);
                box-shadow: 0 8px 20px rgba(93, 69, 52, 0.12);
            }

            .category-chip.is-active {
                background: linear-gradient(135deg, #5d4534 0%, #4a3728 100%);
                color: #fff;
                border-color: #5d4534;
                box-shadow: 0 6px 16px rgba(93, 69, 52, 0.25);
            }

            .category-chip.is-active:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(93, 69, 52, 0.3);
            }

            .category-chip small {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 24px;
                height: 24px;
                padding: 0 8px;
                background: #f3ecde;
                color: #876b4e;
                border-radius: 50px;
                font-size: 11px;
                font-weight: 700;
                transition: all 0.3s ease;
            }

            .category-chip.is-active small {
                background: rgba(255, 255, 255, 0.2);
                color: #fff;
            }

            /* Collection Toolbar */
            .collection-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 16px;
                margin-bottom: 28px;
                padding: 20px 24px;
                background: #fff;
                border-radius: 16px;
                border: 1px solid var(--line);
                box-shadow: 0 2px 12px rgba(93, 69, 52, 0.04);
            }

            .toolbar-left {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .product-count {
                color: var(--ink);
                font-size: 14px;
                font-weight: 600;
            }

            .toolbar-right {
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .toolbar-tagline {
                color: #87917c;
                font-size: 12px;
                letter-spacing: 0.02em;
            }

            .toolbar-tagline span {
                color: #6b7a63;
            }

            /* Sort Dropdown */
            .sort-wrapper {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .sort-label {
                color: #87917c;
                font-size: 12px;
                font-weight: 500;
            }

            .sort-select {
                appearance: none;
                padding: 10px 40px 10px 16px;
                border: 1px solid #e0d6cb;
                border-radius: 10px;
                background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%235d4534' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 12px center;
                color: #5d4534;
                font-size: 13px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .sort-select:hover {
                border-color: #5d4534;
                box-shadow: 0 2px 8px rgba(93, 69, 52, 0.1);
            }

            .sort-select:focus {
                outline: none;
                border-color: #5d4534;
                box-shadow: 0 0 0 3px rgba(93, 69, 52, 0.1);
            }

            /* Empty State */
            .collection-empty {
                grid-column: 1 / -1;
                padding: 80px 20px;
                text-align: center;
                background: #faf5ee;
                border-radius: 20px;
                color: #876b4e;
            }

            .collection-empty p {
                font-size: 15px;
                line-height: 1.8;
            }

            .collection-empty a {
                color: #5d4534;
                font-weight: 600;
                text-decoration: underline;
                text-underline-offset: 3px;
            }

            .collection-empty a:hover {
                color: #4a3728;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .collection-toolbar {
                    flex-direction: column;
                    align-items: stretch;
                    gap: 16px;
                    padding: 16px;
                }

                .toolbar-right {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 12px;
                }

                .sort-wrapper {
                    width: 100%;
                }

                .sort-select {
                    width: 100%;
                }

                .category-chips {
                    gap: 8px;
                    padding-bottom: 20px;
                }

                .category-chip {
                    padding: 8px 14px;
                    font-size: 13px;
                }

                .category-chip small {
                    font-size: 10px;
                    min-width: 20px;
                    height: 20px;
                }
            }

            @media (max-width: 480px) {
                .toolbar-tagline {
                    display: none;
                }
            }
        </style>
    @endpush
@endonce
