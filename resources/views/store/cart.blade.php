@extends('layouts.store')

@push('styles')
<style>
.cart-custom-note {
    margin-top: 6px;
    font-size: 13px;
    color: #7c3aed;
    background: #f5f3ff;
    padding: 4px 10px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
</style>
@endpush

@section('title', 'Giỏ hàng')
@section('content')
    <section class="shopping-page">
        <div class="container">
            <div class="shopping-heading">
                <span class="eyebrow">MÓN QUÀ ĐANG CHỜ BẠN</span>
                <h1>Giỏ hàng <em>xinh xắn.</em></h1>
                <p>Phí giao hàng 30.000đ, miễn phí cho đơn hàng từ 300.000đ.</p>
            </div>

            @if (session('success'))
                <p class="shopping-alert is-success" role="status">{{ session('success') }}</p>
            @endif
            @if (session('warning'))
                <p class="shopping-alert is-warning" role="status">{{ session('warning') }}</p>
            @endif
            @if ($errors->any())
                <div class="shopping-alert is-warning" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if ($items === [])
                <div class="empty-cart">
                    <span aria-hidden="true">♡</span>
                    <h2>Giỏ hàng đang trống</h2>
                    <p>Chọn một món quà thật xinh để bắt đầu nhé.</p>
                    <a class="shopping-primary-button" href="{{ route('products.index') }}">Khám phá sản phẩm</a>
                </div>
            @else
                <form id="cart-bulk-form" method="POST" action="{{ route('cart.bulk-destroy') }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="product_ids" id="cart-selected-ids" value="">
                </form>

                <div class="shopping-layout">
                    <div class="cart-list-section">
                        <div class="cart-header">
                            <label class="cart-select-all">
                                <input type="checkbox" id="cart-select-all" aria-label="Chọn tất cả sản phẩm">
                                <span>Chọn tất cả</span>
                            </label>
                            <button type="button" class="cart-bulk-delete-btn" id="cart-bulk-delete" disabled aria-label="Xóa các sản phẩm đã chọn">
                                <span>Xóa đã chọn</span>
                            </button>
                        </div>

                        <div class="cart-items">
                            @foreach ($items as $item)
                                <article class="cart-item">
                                    <label class="cart-item-select">
                                        <input type="checkbox" name="selected_items[]" value="{{ $item['product']->id }}" aria-label="Chọn {{ $item['product']->name }}">
                                    </label>
                                    @if ($item['image'])
                                        <img src="{{ $item['image'] }}" alt="{{ $item['product']->name }}" loading="lazy">
                                    @else
                                        <div class="cart-item-placeholder" aria-hidden="true">☕</div>
                                    @endif
                                    <div class="cart-item-details">
                                        <h2><a href="{{ route('products.show', $item['product']->slug) }}">{{ $item['product']->name }}</a></h2>
                                        <p>{{ number_format($item['unit_price'], 0, ',', '.') }}đ / sản phẩm</p>
                                        @if (! $item['available'])
                                            <p class="form-error">Chỉ còn {{ $item['product']->stock }} sản phẩm trong kho. Hãy cập nhật số lượng.</p>
                                        @endif
                                        @if (! empty($item['custom_text']))
                                            <p class="cart-custom-note"><span>✏️</span> Text: {{ $item['custom_text'] }}</p>
                                        @endif
                                        @if (! empty($item['custom_image_path']))
                                            <p class="cart-custom-note"><span>🖼️</span> Có ảnh tùy chỉnh</p>
                                        @endif
                                    </div>
                                    <form class="cart-quantity-form" method="POST" action="{{ route('cart.update', $item['product']->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <label class="visually-hidden" for="quantity-{{ $item['product']->id }}">Số lượng {{ $item['product']->name }}</label>
                                        <input id="quantity-{{ $item['product']->id }}" type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="99" required>
                                        <button type="submit">Cập nhật</button>
                                    </form>
                                    <strong class="cart-item-total">{{ number_format($item['line_total'], 0, ',', '.') }}đ</strong>
                                    <form method="POST" action="{{ route('cart.destroy', $item['product']->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="cart-remove-button" type="submit" aria-label="Xóa {{ $item['product']->name }}">Xóa</button>
                                    </form>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <aside class="shopping-summary">
                        <h2>Tóm tắt đơn hàng</h2>
                        <div><span>Tạm tính</span><strong>{{ number_format($subtotal, 0, ',', '.') }}đ</strong></div>
                        <div><span>Giao hàng</span><strong>{{ $subtotal >= 300000 ? 'Miễn phí' : '30.000đ' }}</strong></div>
                        <p>Phí giao hàng chính xác sẽ được xác nhận ở bước thanh toán.</p>
                        @if ($items->every(fn ($item) => $item['available']))
                            <a class="shopping-primary-button" href="{{ route('checkout.index') }}">Tiến hành thanh toán</a>
                        @else
                            <button class="shopping-primary-button" type="button" disabled>Cập nhật số lượng để tiếp tục</button>
                        @endif
                        <a class="shopping-secondary-link" href="{{ route('products.index') }}">Tiếp tục mua sắm</a>
                    </aside>
                </div>
            @endif
        </div>
    </section>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('cart-select-all');
        const itemCheckboxes = document.querySelectorAll('input[name="selected_items[]"]');
        const bulkDeleteBtn = document.getElementById('cart-bulk-delete');
        const bulkForm = document.getElementById('cart-bulk-form');
        const selectedIdsInput = document.getElementById('cart-selected-ids');

        if (!selectAllCheckbox || !bulkDeleteBtn) return;

        const updateBulkButton = () => {
            const checkedItems = document.querySelectorAll('input[name="selected_items[]"]:checked');
            const count = checkedItems.length;

            bulkDeleteBtn.disabled = count === 0;

            if (count > 0) {
                bulkDeleteBtn.querySelector('span').textContent = 'Xóa đã chọn' + (count > 1 ? ' (' + count + ')' : '');
            } else {
                bulkDeleteBtn.querySelector('span').textContent = 'Xóa đã chọn';
            }
        };

        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkButton();
        });

        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);

                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
                updateBulkButton();
            });
        });

        bulkDeleteBtn.addEventListener('click', function() {
            const checkedItems = document.querySelectorAll('input[name="selected_items[]"]:checked');

            if (checkedItems.length === 0) return;

            const productNames = Array.from(checkedItems).map(cb => {
                const article = cb.closest('.cart-item');
                const nameEl = article?.querySelector('h2 a');
                return nameEl?.textContent?.trim() || 'sản phẩm';
            });

            const message = checkedItems.length === 1
                ? 'Xóa "' + productNames[0] + '" khỏi giỏ hàng?'
                : 'Xóa ' + checkedItems.length + ' sản phẩm đã chọn khỏi giỏ hàng?';

            if (!confirm(message)) return;

            const ids = Array.from(checkedItems).map(cb => cb.value);
            selectedIdsInput.value = JSON.stringify(ids);
            bulkForm.submit();
        });
    });
    </script>
@endsection
