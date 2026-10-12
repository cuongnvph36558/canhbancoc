@extends('layouts.admin')

@section('title', 'Đánh giá sản phẩm')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">CHĂM SÓC KHÁCH HÀNG</span>
            <h1>Đánh giá sản phẩm</h1>
            <p>Theo dõi phản hồi của khách hàng và xóa đánh giá không phù hợp.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success" role="status">{{ session('success') }}</div>
    @endif

    <section class="admin-products-panel" aria-labelledby="reviews-heading">
        <div class="admin-panel-heading admin-order-list-heading">
            <div>
                <h2 id="reviews-heading">Danh sách đánh giá</h2>
                <p>Đánh giá mới nhất được hiển thị trước.</p>
            </div>
            <form class="admin-order-filters" method="GET" action="{{ route('admin.product-reviews.index') }}">
                <label class="sr-only" for="review-search">Tìm theo sản phẩm, khách hàng hoặc nội dung đánh giá</label>
                <input id="review-search" type="search" name="search" value="{{ $search }}" placeholder="Sản phẩm, khách hàng, nội dung">
                <label class="sr-only" for="review-rating-filter">Lọc theo số sao</label>
                <select id="review-rating-filter" name="rating">
                    <option value="">Tất cả số sao</option>
                    @foreach (range(5, 1) as $rating)
                        <option value="{{ $rating }}" @selected((string) $selectedRating === (string) $rating)>{{ $rating }} sao</option>
                    @endforeach
                </select>
                <button class="admin-action-button" type="submit">Lọc đánh giá</button>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Khách hàng</th>
                        <th>Đánh giá</th>
                        <th>Nội dung</th>
                        <th>Thời gian</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td><strong>{{ $review->product->name }}</strong></td>
                            <td>
                                <strong>{{ $review->user->name }}</strong>
                                <small>{{ $review->user->email }}</small>
                            </td>
                            <td><span aria-label="{{ $review->rating }} trên 5 sao">{{ $review->rating }}/5 ★</span></td>
                            <td>{{ $review->comment ?: 'Không có nhận xét.' }}</td>
                            <td>{{ $review->created_at->format('H:i d/m/Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.product-reviews.destroy', $review) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="admin-action-button is-danger" type="submit">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-empty-state">Không tìm thấy đánh giá phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">{{ $reviews->links() }}</div>
    </section>
@endsection
