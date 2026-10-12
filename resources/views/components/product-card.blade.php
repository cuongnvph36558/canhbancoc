<article class="product-card" data-cup-product data-category="{{ $product['category'] }}" data-name="{{ $product['name'] }}">
    <a class="product-image" href="{{ route('products.show', $product['slug']) }}">
        @if ($product['image'])
            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy">
        @else
            <span class="product-image-placeholder" aria-hidden="true">☕</span>
        @endif
        @if (!empty($product['badge']))
            <span class="product-badge">{{ $product['badge'] }}</span>
        @endif
        @if (($product['discount_percentage'] ?? 0) > 0)
            <span class="product-discount-badge">-{{ $product['discount_percentage'] }}%</span>
        @endif
        <span class="product-view" aria-hidden="true">↗</span>
    </a>
    <div class="product-meta">
        <a class="product-category-link" href="{{ ! empty($product['category_slug']) ? route('products.category', $product['category_slug']) : route('products.index') }}">{{ $product['category'] }}</a>
        <span class="product-pricing">
            @if (($product['discount_percentage'] ?? 0) > 0)
                <s class="product-price-original">{{ number_format($product['price'], 0, ',', '.') }}đ</s>
            @endif
            <span class="product-price">{{ number_format($product['discounted_price'] ?? $product['price'], 0, ',', '.') }}đ</span>
        </span>
    </div>
    <h3><a href="{{ route('products.show', $product['slug']) }}">{{ $product['name'] }}</a></h3>
    <a class="cup-product-link" href="{{ route('products.show', $product['slug']) }}">Xem chi tiết <span aria-hidden="true">↗</span></a>
</article>
