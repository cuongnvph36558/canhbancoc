<article class="product-card" data-cup-product data-category="{{ $product['category'] }}" data-name="{{ $product['name'] }}">
    <a class="product-image" href="{{ route('products.show', $product['slug']) }}">
        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy">
        @if ($product['badge'])
            <span class="product-badge">{{ $product['badge'] }}</span>
        @endif
        <span class="product-view" aria-hidden="true">↗</span>
    </a>
    <div class="product-meta">
        <span>{{ $product['category'] }}</span>
        <span class="product-price">{{ number_format($product['price'], 0, ',', '.') }}đ</span>
    </div>
    <h3><a href="{{ route('products.show', $product['slug']) }}">{{ $product['name'] }}</a></h3>
    <a class="cup-product-link" href="{{ route('products.show', $product['slug']) }}">Xem chi tiết <span aria-hidden="true">↗</span></a>
</article>
