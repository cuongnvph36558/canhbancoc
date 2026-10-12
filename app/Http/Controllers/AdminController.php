<?php

namespace App\Http\Controllers;

use App\Models\CustomCupOrder;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $users = User::query()
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'client' => User::where('role', 'client')->count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        $standardRevenue = (float) Order::query()
            ->where('payment_status', Order::PAYMENT_PAID)
            ->sum('total');

        $customCupRevenue = (float) CustomCupOrder::query()
            ->whereNotIn('status', ['pending', 'cancelled'])
            ->sum('total_amount');

        $monthRevenue = (float) Order::query()
            ->where('payment_status', Order::PAYMENT_PAID)
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total') + (float) CustomCupOrder::query()
            ->whereNotIn('status', ['pending', 'cancelled'])
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total_amount');

        $todayRevenue = (float) Order::query()
            ->where('payment_status', Order::PAYMENT_PAID)
            ->whereBetween('paid_at', [now()->startOfDay(), now()->endOfDay()])
            ->sum('total') + (float) CustomCupOrder::query()
            ->whereNotIn('status', ['pending', 'cancelled'])
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->sum('total_amount');

        $revenueStats = [
            'total' => $standardRevenue + $customCupRevenue,
            'standard' => $standardRevenue,
            'custom' => $customCupRevenue,
            'month' => $monthRevenue,
            'today' => $todayRevenue,
        ];

        return view('admin.users', [
            'users' => $users,
            'stats' => $stats,
            'revenueStats' => $revenueStats,
            'search' => $validated['search'] ?? '',
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:client,admin'],
        ]);

        if ($user->is(auth()->user())) {
            return back()->with('error', 'Bạn không thể thay đổi vai trò của chính mình.');
        }

        $user->update(['role' => $validated['role']]);

        return back()->with('success', 'Vai trò tài khoản đã được cập nhật.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Bạn không thể khóa tài khoản của chính mình.');
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return back()->with('success', 'Trạng thái tài khoản đã được cập nhật.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Bạn không thể xóa tài khoản của chính mình.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Tài khoản đã được xóa.');
    }
}
