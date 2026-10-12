@extends('layouts.admin')

@section('title', 'Quản lý phân loại sản phẩm')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">QUẢN LÝ KHO</span>
            <h1>Phân loại sản phẩm</h1>
            <p>Nhóm các sản phẩm theo từng dòng cốc để khách dễ tìm và admin dễ vận hành.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="admin-btn-primary">+ Thêm phân loại</a>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success" role="status">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="admin-alert admin-alert-error" role="alert">{{ session('error') }}</div>
    @endif

    <div class="admin-summary">
        <div class="admin-stat-card">
            <span>Tổng phân loại</span>
            <strong>{{ $stats['total'] }}</strong>
            <small>Tất cả danh mục trong hệ thống</small>
        </div>
        <div class="admin-stat-card">
            <span>Đang hiển thị</span>
            <strong>{{ $stats['active'] }}</strong>
            <small>Phân loại hiện đang bật</small>
        </div>
        <div class="admin-stat-card">
            <span>Đã ẩn</span>
            <strong>{{ $stats['inactive'] }}</strong>
            <small>Phân loại không hiển thị</small>
        </div>
        <div class="admin-stat-card">
            <span>Đang được dùng</span>
            <strong>{{ $stats['used'] }}</strong>
            <small>Phân loại đã gắn sản phẩm</small>
        </div>
    </div>

    <section class="admin-products-panel" aria-labelledby="categories-heading">
        <div class="admin-panel-heading">
            <div>
                <h2 id="categories-heading">Danh sách phân loại</h2>
                <p>Sắp xếp, chỉnh sửa hoặc xóa phân loại sản phẩm.</p>
            </div>
            <form class="admin-search" method="GET" action="{{ route('admin.categories.index') }}">
                <input type="search" name="search" value="{{ $search }}" placeholder="Tìm theo tên hoặc slug" aria-label="Tìm phân loại">
                <select name="status" aria-label="Lọc theo trạng thái">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" @selected($status === 'active')>Đang hiển thị</option>
                    <option value="inactive" @selected($status === 'inactive')>Đã ẩn</option>
                </select>
                <button type="submit">Tìm kiếm</button>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Phân loại</th>
                        <th>Slug</th>
                        <th>Số sản phẩm</th>
                        <th>Thứ tự</th>
                        <th>Trạng thái</th>
                        <th class="admin-actions-heading">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>
                                <div class="admin-category-cell">
                                    <span class="admin-category-icon" aria-hidden="true">{{ $category->icon ?: '🗂' }}</span>
                                    <span>
                                        <strong>{{ $category->name }}</strong>
                                        @if ($category->description)
                                            <small>{{ Str::limit($category->description, 60) }}</small>
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td>
                                <span class="admin-stock">{{ $category->products_count ?? 0 }}</span>
                            </td>
                            <td>{{ $category->position }}</td>
                            <td>
                                <span @class(['admin-state', 'is-active' => $category->is_active, 'is-inactive' => ! $category->is_active])>
                                    <span aria-hidden="true"></span>
                                    {{ $category->is_active ? 'Đang hiển thị' : 'Đã ẩn' }}
                                </span>
                            </td>
                            <td>
                                <div class="admin-row-actions">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="admin-action-button is-secondary">Chỉnh sửa</a>

                                    <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}" class="admin-inline-form">
                                        @csrf
                                        <button class="admin-action-button is-secondary" type="submit">
                                            {{ $category->is_active ? 'Ẩn' : 'Hiển thị' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phân loại này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="admin-action-button is-danger" type="submit" @disabled(($category->products_count ?? 0) > 0)>Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-empty-state">
                                {{ $search || $status ? 'Không tìm thấy phân loại phù hợp.' : 'Chưa có phân loại nào. Hãy tạo phân loại đầu tiên để đa dạng hàng hóa.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            {{ $categories->links() }}
        </div>
    </section>
@endsection

<style>
.admin-category-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.admin-category-icon {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    background: #e3f2fd;
    color: #1565c0;
    display: grid;
    place-items: center;
    font-size: 18px;
}

.admin-inline-form {
    display: contents;
}

.admin-page-heading {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
}

.admin-btn-primary {
    padding: 12px 20px;
    background: #2e7d32;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: background 0.2s;
    display: inline-block;
}

.admin-btn-primary:hover {
    background: #1b5e20;
}
</style>
