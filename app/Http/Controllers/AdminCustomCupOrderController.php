<?php

namespace App\Http\Controllers;

use App\Models\CustomCupOrder;
use App\Services\CustomCupOrderPreview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminCustomCupOrderController extends Controller
{
    private const STATUS_LABELS = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'processing' => 'Đang chuẩn bị',
        'in_production' => 'Đang sản xuất',
        'shipped' => 'Đang giao',
        'delivered' => 'Đã giao',
        'completed' => 'Hoàn tất',
        'cancelled' => 'Đã hủy',
    ];

    public function index(): View
    {
        return view('admin.custom-cup-orders.index', [
            'orders' => CustomCupOrder::query()
                ->latest('id')
                ->paginate(15),
            'pendingCount' => CustomCupOrder::query()->where('status', 'pending')->count(),
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function show(CustomCupOrder $order): View
    {
        return view('admin.custom-cup-orders.show', [
            'order' => $order,
            'cupVariant' => config("store.custom_cup.variants.{$order->cup_variant}"),
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function updateStatus(Request $request, CustomCupOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(array_keys(self::STATUS_LABELS))],
        ]);

        if (in_array($order->status, ['delivered', 'completed', 'cancelled'], true) && $validated['status'] !== $order->status) {
            return back()->withErrors([
                'status' => 'Đơn hàng đã kết thúc và không thể thay đổi trạng thái.',
            ]);
        }

        $order->update(['status' => $validated['status']]);

        return back()->with('success', 'Trạng thái đơn cốc custom đã được cập nhật.');
    }

    public function preview(CustomCupOrder $order, CustomCupOrderPreview $preview): BinaryFileResponse|Response
    {
        $disk = Storage::disk('local');

        if ($order->preview_path !== null && $disk->exists($order->preview_path)) {
            $mimeType = match (strtolower(pathinfo($order->preview_path, PATHINFO_EXTENSION))) {
                'svg' => 'image/svg+xml',
                default => 'image/png',
            };

            return response()->file($disk->path($order->preview_path), [
                'Content-Type' => $mimeType,
            ]);
        }

        return response($preview->render($order), 200, [
            'Content-Type' => 'image/svg+xml',
        ]);
    }

    public function downloadArtwork(CustomCupOrder $order): StreamedResponse
    {
        abort_if($order->artwork_path === null, 404);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($order->artwork_path), 404, 'Không tìm thấy hình in của đơn hàng.');

        return $disk->download($order->artwork_path);
    }
}
