<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\VietQrCodeGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    private const STATUS_LABELS = [
        Order::STATUS_PENDING => 'Chờ xử lý',
        Order::STATUS_PROCESSING => 'Đang chuẩn bị',
        Order::STATUS_SHIPPED => 'Đang giao',
        Order::STATUS_DELIVERED => 'Đã giao',
        Order::STATUS_CANCELLED => 'Đã hủy',
        Order::STATUS_EXPIRED => 'Hết hạn',
        Order::STATUS_NEEDS_REVIEW => 'Cần kiểm tra',
    ];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', Rule::in(array_keys(self::STATUS_LABELS))],
            'sort' => ['nullable', 'string', Rule::in(['newest', 'oldest', 'price_asc', 'price_desc'])],
        ]);

        $sortColumn = match ($validated['sort'] ?? 'newest') {
            'oldest' => 'id',
            'price_asc' => 'total',
            'price_desc' => 'total',
            default => 'id',
        };
        $sortDirection = match ($validated['sort'] ?? 'newest') {
            'oldest' => 'asc',
            'price_asc' => 'asc',
            'price_desc' => 'desc',
            default => 'desc',
        };

        $orders = Order::query()
            ->withCount('items')
            ->with('items:id,order_id,product_image,product_name')
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%");
                });
            })
            ->when($validated['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('order_status', $status))
            ->orderBy($sortColumn, $sortDirection)
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'search' => $validated['search'] ?? '',
            'selectedStatus' => $validated['status'] ?? '',
            'selectedSort' => $validated['sort'] ?? 'newest',
            'statusLabels' => self::STATUS_LABELS,
            'totalOrders' => Order::query()->count(),
            'pendingOrders' => Order::query()->where('order_status', Order::STATUS_PENDING)->count(),
            'unpaidCodOrders' => Order::query()
                ->where('payment_method', Order::PAYMENT_METHOD_COD)
                ->where('payment_status', Order::PAYMENT_PENDING)
                ->whereNotIn('order_status', [Order::STATUS_CANCELLED, Order::STATUS_EXPIRED])
                ->count(),
        ]);
    }

    public function show(Order $order, VietQrCodeGenerator $vietQrCodeGenerator): View
    {
        $editableStatuses = [
            Order::STATUS_PENDING => self::STATUS_LABELS[Order::STATUS_PENDING],
            Order::STATUS_CANCELLED => self::STATUS_LABELS[Order::STATUS_CANCELLED],
        ];

        if (
            $order->payment_method === Order::PAYMENT_METHOD_COD
            || $order->payment_status === Order::PAYMENT_PAID
        ) {
            $editableStatuses = [
                Order::STATUS_PENDING => self::STATUS_LABELS[Order::STATUS_PENDING],
                Order::STATUS_PROCESSING => self::STATUS_LABELS[Order::STATUS_PROCESSING],
                Order::STATUS_SHIPPED => self::STATUS_LABELS[Order::STATUS_SHIPPED],
                Order::STATUS_DELIVERED => self::STATUS_LABELS[Order::STATUS_DELIVERED],
                Order::STATUS_CANCELLED => self::STATUS_LABELS[Order::STATUS_CANCELLED],
            ];
        }

        if ($order->order_status === Order::STATUS_NEEDS_REVIEW) {
            $editableStatuses = [Order::STATUS_NEEDS_REVIEW => self::STATUS_LABELS[Order::STATUS_NEEDS_REVIEW]] + $editableStatuses;
        }

        if ($order->order_status === Order::STATUS_SHIPPED) {
            unset($editableStatuses[Order::STATUS_CANCELLED]);
        }

        if ($order->payment_status === Order::PAYMENT_PAID) {
            unset($editableStatuses[Order::STATUS_CANCELLED]);
        }

        if (! array_key_exists($order->order_status, $editableStatuses) && isset(self::STATUS_LABELS[$order->order_status])) {
            $editableStatuses = [$order->order_status => self::STATUS_LABELS[$order->order_status]] + $editableStatuses;
        }

        return view('admin.orders.show', [
            'order' => $order->load('items:id,order_id,product_name,product_sku,product_image,unit_price,quantity,line_total'),
            'statusLabels' => self::STATUS_LABELS,
            'paymentMethodLabel' => match ($order->payment_method) {
                Order::PAYMENT_METHOD_COD => 'Thanh toán khi nhận hàng (COD)',
                Order::PAYMENT_METHOD_BANK_QR => 'Chuyển khoản ngân hàng qua QR',
                default => 'Thanh toán khi nhận hàng (COD)',
            },
            'paymentStatusLabel' => match ($order->payment_status) {
                Order::PAYMENT_PAID => 'Đã thanh toán',
                Order::PAYMENT_FAILED => 'Thanh toán thất bại',
                default => 'Chưa thanh toán',
            },
            'bankQrImageUrl' => $order->payment_method === Order::PAYMENT_METHOD_BANK_QR
                ? $vietQrCodeGenerator->imageUrl($order)
                : null,
            'editableStatuses' => $editableStatuses,
        ]);
    }

    public function confirmBankQrPayment(Order $order): RedirectResponse
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (
                $lockedOrder->payment_method !== Order::PAYMENT_METHOD_BANK_QR
                || $lockedOrder->payment_status !== Order::PAYMENT_PENDING
                || in_array($lockedOrder->order_status, [
                    Order::STATUS_CANCELLED,
                    Order::STATUS_EXPIRED,
                    Order::STATUS_DELIVERED,
                ], true)
            ) {
                throw ValidationException::withMessages([
                    'payment' => 'Chỉ xác nhận được đơn chuyển khoản QR đang chờ thanh toán.',
                ]);
            }

            $lockedOrder->payment_status = Order::PAYMENT_PAID;
            $lockedOrder->paid_at = now();
            $lockedOrder->save();
        });

        return back()->with('success', 'Đã xác nhận thanh toán chuyển khoản QR.');
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $allowedStatuses = [
            Order::STATUS_PENDING,
            Order::STATUS_PROCESSING,
            Order::STATUS_SHIPPED,
            Order::STATUS_DELIVERED,
            Order::STATUS_CANCELLED,
        ];

        if ($order->order_status === Order::STATUS_NEEDS_REVIEW) {
            $allowedStatuses[] = Order::STATUS_NEEDS_REVIEW;
        }

        $validated = $request->validate([
            'order_status' => [
                'required',
                'string',
                Rule::in($allowedStatuses),
            ],
        ]);

        DB::transaction(function () use ($order, $validated): void {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $newStatus = $validated['order_status'];

            if (
                in_array($lockedOrder->order_status, [
                    Order::STATUS_CANCELLED,
                    Order::STATUS_EXPIRED,
                    Order::STATUS_DELIVERED,
                ], true)
                && $newStatus !== $lockedOrder->order_status
            ) {
                throw ValidationException::withMessages([
                    'order_status' => 'Đơn hàng đã kết thúc và không thể thay đổi trạng thái.',
                ]);
            }

            if (
                $newStatus === Order::STATUS_CANCELLED
                && $lockedOrder->order_status === Order::STATUS_SHIPPED
            ) {
                throw ValidationException::withMessages([
                    'order_status' => 'Không thể hủy đơn hàng đang được giao.',
                ]);
            }

            if ($newStatus === Order::STATUS_CANCELLED && $lockedOrder->payment_status === Order::PAYMENT_PAID) {
                throw ValidationException::withMessages([
                    'order_status' => 'Không thể hủy đơn hàng đã thanh toán.',
                ]);
            }

            if (
                in_array($newStatus, [
                    Order::STATUS_PROCESSING,
                    Order::STATUS_SHIPPED,
                    Order::STATUS_DELIVERED,
                ], true)
                && $lockedOrder->payment_status !== Order::PAYMENT_PAID
                && $lockedOrder->payment_method !== Order::PAYMENT_METHOD_COD
            ) {
                throw ValidationException::withMessages([
                    'order_status' => 'Chỉ xử lý đơn thanh toán trước sau khi đã xác nhận thanh toán.',
                ]);
            }

            if ($newStatus === Order::STATUS_CANCELLED && $lockedOrder->inventory_reserved) {
                foreach ($lockedOrder->items()->orderBy('product_id')->get() as $item) {
                    if ($item->product_id === null) {
                        continue;
                    }

                    $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();
                    $product?->increment('stock', $item->quantity);
                }

                $lockedOrder->inventory_reserved = false;
            }

            if (
                $newStatus === Order::STATUS_DELIVERED
                && $lockedOrder->payment_method === Order::PAYMENT_METHOD_COD
                && $lockedOrder->payment_status !== Order::PAYMENT_PAID
            ) {
                $lockedOrder->payment_status = Order::PAYMENT_PAID;
                $lockedOrder->paid_at = now();
                $lockedOrder->inventory_reserved = false;
            }

            $lockedOrder->order_status = $newStatus;
            $lockedOrder->save();
        });

        return back()->with('success', 'Trạng thái đơn hàng đã được cập nhật.');
    }
}
