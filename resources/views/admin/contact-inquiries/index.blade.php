@extends('layouts.admin')

@section('title', 'Lời nhắn liên hệ')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">CHĂM SÓC KHÁCH HÀNG</span>
            <h1>Lời nhắn liên hệ</h1>
            <p>Tiếp nhận và theo dõi các câu hỏi khách gửi từ trang liên hệ.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success" role="status">{{ session('success') }}</div>
    @endif

    <div class="admin-summary">
        <div class="admin-stat-card"><span>Mới</span><strong>{{ $newCount }}</strong><small>Chưa bắt đầu xử lý</small></div>
        <div class="admin-stat-card"><span>Đang xử lý</span><strong>{{ $inProgressCount }}</strong><small>Đang cần phản hồi</small></div>
        <div class="admin-stat-card"><span>Đã đóng</span><strong>{{ $closedCount }}</strong><small>Đã hoàn tất hỗ trợ</small></div>
    </div>

    <section class="admin-products-panel" aria-labelledby="inquiries-heading">
        <div class="admin-panel-heading admin-order-list-heading">
            <div>
                <h2 id="inquiries-heading">Hộp thư liên hệ</h2>
                <p>Lời nhắn mới nhất được hiển thị trước.</p>
            </div>
            <form class="admin-order-filters" method="GET" action="{{ route('admin.contact-inquiries.index') }}">
                <label class="sr-only" for="inquiry-status-filter">Lọc lời nhắn theo trạng thái</label>
                <select id="inquiry-status-filter" name="status">
                    <option value="">Tất cả trạng thái</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="admin-action-button" type="submit">Lọc lời nhắn</button>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Khách hàng</th>
                        <th>Chủ đề</th>
                        <th>Trạng thái</th>
                        <th>Thời gian</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inquiries as $inquiry)
                        <tr>
                            <td>
                                <strong>{{ $inquiry->name }}</strong>
                                <small>{{ $inquiry->email }}{{ $inquiry->phone ? ' · '.$inquiry->phone : '' }}</small>
                            </td>
                            <td>{{ $inquiry->subject }}</td>
                            <td><span class="admin-state">{{ $statusLabels[$inquiry->status] ?? $inquiry->status }}</span></td>
                            <td>{{ $inquiry->created_at->format('H:i d/m/Y') }}</td>
                            <td><a href="{{ route('admin.contact-inquiries.show', $inquiry) }}" class="admin-action-button">Xem lời nhắn</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="admin-empty-state">Chưa có lời nhắn nào phù hợp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">{{ $inquiries->links() }}</div>
    </section>
@endsection
