@extends('layouts.admin')

@section('title', 'Quản lý khách hàng')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">TÀI KHOẢN CLIENT</span>
            <h1>Khách hàng & phân quyền</h1>
            <p>Quản lý tài khoản client, vai trò truy cập và trạng thái hoạt động.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success" role="status">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="admin-alert admin-alert-error" role="alert">{{ session('error') }}</div>
    @endif

    <div class="admin-summary">
        <div class="admin-stat-card">
            <span>Tổng tài khoản</span>
            <strong>{{ $stats['total'] }}</strong>
            <small>Đã đăng ký trên client</small>
        </div>
        <div class="admin-stat-card">
            <span>Client</span>
            <strong>{{ $stats['client'] }}</strong>
            <small>Tài khoản khách hàng</small>
        </div>
        <div class="admin-stat-card">
            <span>Quản trị viên</span>
            <strong>{{ $stats['admin'] }}</strong>
            <small>Có quyền truy cập admin</small>
        </div>
        <div class="admin-stat-card">
            <span>Đang hoạt động</span>
            <strong>{{ $stats['active'] }}</strong>
            <small>{{ $stats['inactive'] }} tài khoản đã khóa</small>
        </div>
    </div>

    <div class="admin-summary">
        <div class="admin-stat-card">
            <span>Doanh thu tổng</span>
            <strong>{{ number_format($revenueStats['total'], 0, ',', '.') }}₫</strong>
            <small>Tổng từ đơn thường và custom</small>
        </div>
        <div class="admin-stat-card">
            <span>Đơn thường</span>
            <strong>{{ number_format($revenueStats['standard'], 0, ',', '.') }}₫</strong>
            <small>Đã thanh toán</small>
        </div>
        <div class="admin-stat-card">
            <span>Đơn custom</span>
            <strong>{{ number_format($revenueStats['custom'], 0, ',', '.') }}₫</strong>
            <small>Đã xác nhận xử lý</small>
        </div>
        <div class="admin-stat-card">
            <span>Tháng này</span>
            <strong>{{ number_format($revenueStats['month'], 0, ',', '.') }}₫</strong>
            <small>Hôm nay {{ number_format($revenueStats['today'], 0, ',', '.') }}₫</small>
        </div>
    </div>

    <section class="admin-users-panel" aria-labelledby="users-heading">
        <div class="admin-panel-heading">
            <div>
                <h2 id="users-heading">Danh sách tài khoản</h2>
                <p>Chọn role để cấp hoặc thu hồi quyền quản trị.</p>
            </div>
            <form class="admin-search" method="GET" action="{{ route('admin.users') }}">
                <label class="sr-only" for="admin-search">Tìm theo tên hoặc email</label>
                <input id="admin-search" type="search" name="search" value="{{ $search }}" placeholder="Tìm tên hoặc email">
                <button type="submit">Tìm kiếm</button>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Khách hàng</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tham gia</th>
                        <th class="admin-actions-heading">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="admin-user-cell">
                                    <span class="admin-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                    <span>
                                        <strong>{{ $user->name }}</strong>
                                        <small>{{ $user->email }}</small>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span @class(['admin-role-badge', 'is-admin' => $user->role === 'admin'])>
                                    {{ $user->role === 'admin' ? 'Admin' : 'Client' }}
                                </span>
                            </td>
                            <td>
                                <span @class(['admin-state', 'is-active' => $user->is_active, 'is-inactive' => ! $user->is_active])>
                                    <span aria-hidden="true"></span>
                                    {{ $user->is_active ? 'Hoạt động' : 'Đã khóa' }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                @if (auth()->id() !== $user->id)
                                    <div class="admin-row-actions">
                                        <form method="POST" action="{{ route('admin.users.update-role', $user) }}" class="admin-role-form">
                                            @csrf
                                            @method('PUT')
                                            <label class="sr-only" for="role-{{ $user->id }}">Role cho {{ $user->name }}</label>
                                            <select id="role-{{ $user->id }}" name="role">
                                                <option value="client" @selected($user->role === 'client')>Client</option>
                                                <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                            </select>
                                            <button class="admin-action-button" type="submit">Lưu role</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                            @csrf
                                            <button class="admin-action-button is-secondary" type="submit">
                                                {{ $user->is_active ? 'Khóa' : 'Mở khóa' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="admin-action-button is-danger" type="submit">Xóa</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="admin-self-label">Tài khoản của bạn</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="admin-empty-state">
                                {{ $search ? 'Không tìm thấy tài khoản phù hợp.' : 'Chưa có tài khoản nào.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            {{ $users->links() }}
        </div>
    </section>
@endsection
