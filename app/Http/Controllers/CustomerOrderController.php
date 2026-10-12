<?php

namespace App\Http\Controllers;

use App\Models\CustomCupOrder;
use App\Models\Order;
use App\Services\CustomCupOrderPreview;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerOrderController extends Controller
{
    private const ORDER_STATUS_LABELS = [
        Order::STATUS_PENDING => 'Chờ xác nhận',
        Order::STATUS_PROCESSING => 'Đang chuẩn bị',
        Order::STATUS_SHIPPED => 'Đang giao',
        Order::STATUS_DELIVERED => 'Đã giao',
        Order::STATUS_CANCELLED => 'Đã hủy',
        Order::STATUS_EXPIRED => 'Đã hết hạn',
        Order::STATUS_NEEDS_REVIEW => 'Cần xác nhận với cửa hàng',
    ];

    private const CUSTOM_ORDER_STATUS_LABELS = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'processing' => 'Đang chuẩn bị',
        'in_production' => 'Đang sản xuất',
        'shipped' => 'Đang giao',
        'delivered' => 'Đã giao',
        'completed' => 'Hoàn tất',
        'cancelled' => 'Đã hủy',
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $searchQuery = $request->input('q');
        $statusFilter = $request->input('status');
        $sortOption = $request->input('sort', 'newest');

        $ordersQuery = Order::query()
            ->where('user_id', $user->id)
            ->with('items:id,order_id,product_name,product_image,quantity,line_total');

        $customOrdersQuery = CustomCupOrder::query()
            ->where('user_id', $user->id);

        if ($searchQuery) {
            $ordersQuery->where('order_number', 'like', "%{$searchQuery}%");
            $customOrdersQuery->where('order_number', 'like', "%{$searchQuery}%");
        }

        $sortColumn = match ($sortOption) {
            'oldest' => 'id',
            'price_asc' => 'total',
            'price_desc' => 'total',
            default => 'id',
        };
        $sortDirection = match ($sortOption) {
            'oldest' => 'asc',
            'price_asc' => 'asc',
            'price_desc' => 'desc',
            default => 'desc',
        };

        if ($statusFilter) {
            $ordersQuery->where('order_status', $statusFilter);
        }

        $orders = $ordersQuery->orderBy($sortColumn, $sortDirection)->paginate(10, ['*'], 'orders_page')->withQueryString();
        $customOrders = $customOrdersQuery->latest('id')->paginate(10, ['*'], 'custom_orders_page')->withQueryString();

        return view('store.orders.index', [
            'orders' => $orders,
            'customOrders' => $customOrders,
            'orderStatusLabels' => self::ORDER_STATUS_LABELS,
            'customOrderStatusLabels' => self::CUSTOM_ORDER_STATUS_LABELS,
        ]);
    }

    public function showCustomOrder(Request $request, CustomCupOrder $customCupOrder): View
    {
        $customCupOrder = CustomCupOrder::query()
            ->where('user_id', $request->user()->id)
            ->whereKey($customCupOrder->getKey())
            ->firstOrFail();

        return view('store.orders.custom-show', [
            'order' => $customCupOrder,
            'cupVariant' => config("store.custom_cup.variants.{$customCupOrder->cup_variant}"),
            'statusLabel' => self::CUSTOM_ORDER_STATUS_LABELS[$customCupOrder->status] ?? $customCupOrder->status,
            'previewUrl' => route('customer.custom-cup-orders.preview', $customCupOrder),
        ]);
    }

    public function customOrderPreview(
        Request $request,
        CustomCupOrder $customCupOrder,
        CustomCupOrderPreview $preview,
    ): BinaryFileResponse|Response {
        $customCupOrder = CustomCupOrder::query()
            ->where('user_id', $request->user()->id)
            ->whereKey($customCupOrder->getKey())
            ->firstOrFail();

        $disk = Storage::disk('local');

        if ($customCupOrder->preview_path !== null && $disk->exists($customCupOrder->preview_path)) {
            $mimeType = match (strtolower(pathinfo($customCupOrder->preview_path, PATHINFO_EXTENSION))) {
                'svg' => 'image/svg+xml',
                default => 'image/png',
            };

            return response()->file($disk->path($customCupOrder->preview_path), [
                'Content-Type' => $mimeType,
            ]);
        }

        return response($preview->render($customCupOrder), 200, [
            'Content-Type' => 'image/svg+xml',
        ]);
    }
}
