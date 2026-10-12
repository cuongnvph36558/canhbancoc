<?php

namespace App\Http\Controllers;

use App\Events\NewOrderCreated;
use App\Http\Requests\StoreCustomCupOrderRequest;
use App\Models\CustomCupOrder;
use App\Services\CustomCupConfig;
use App\Services\CustomCupOrderPreview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class CustomCupOrderController extends Controller
{
    public function store(StoreCustomCupOrderRequest $request, CustomCupConfig $config): RedirectResponse
    {
        $validated = $request->validated();
        $quantity = $validated['quantity'];
        $customCup = $config->all();
        $unitPrice = (int) $customCup['unit_price'];
        $discountPercentage = $quantity >= (int) $customCup['bulk_discount_minimum']
            ? (int) $customCup['bulk_discount_percentage']
            : 0;
        $artworkPath = null;
        $previewPath = null;

        try {
            $order = DB::transaction(function () use ($request, $validated, $quantity, $customCup, $unitPrice, $discountPercentage, &$artworkPath, &$previewPath): CustomCupOrder {
                $order = CustomCupOrder::create([
                    'user_id' => $request->user()?->id,
                    'order_number' => 'CM-'.now()->format('ymd').'-'.Str::upper(Str::random(7)),
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'],
                    'customer_email' => $validated['customer_email'] ?? null,
                    'shipping_province' => $validated['shipping_province'],
                    'shipping_ward' => $validated['shipping_ward'],
                    'shipping_address' => $validated['shipping_address'],
                    'cup_variant' => $validated['cup_variant'],
                    'cup_size' => $customCup['size'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_percentage' => $discountPercentage,
                    'total_amount' => (int) round($unitPrice * $quantity * (100 - $discountPercentage) / 100),
                    'design' => $validated['design'],
                    'status' => 'pending',
                ]);

                if ($request->hasFile('artwork')) {
                    $artworkPath = $request->file('artwork')->store('custom-cup-artwork', 'local');

                    if ($artworkPath === false) {
                        throw new RuntimeException('Không thể lưu hình in của đơn hàng.');
                    }

                    $order->update(['artwork_path' => $artworkPath]);
                }

                if (! empty($validated['design_preview'])) {
                    $previewData = preg_replace('/^data:image\/\w+;base64,/', '', $validated['design_preview']);

                    if ($previewData === null) {
                        throw new RuntimeException('Không thể tạo ảnh preview của cốc custom.');
                    }

                    $previewBinary = base64_decode($previewData, true);

                    if ($previewBinary === false) {
                        throw new RuntimeException('Ảnh preview cốc custom không hợp lệ.');
                    }

                    $previewName = $order->order_number.'.png';
                    $previewPath = 'custom-cup-preview/'.$previewName;
                    $saved = Storage::disk('local')->put($previewPath, $previewBinary);

                    if ($saved === false) {
                        throw new RuntimeException('Không thể lưu ảnh preview cốc custom.');
                    }

                    $order->update(['preview_path' => $previewPath]);
                }

                return $order;
            });
        } catch (Throwable $exception) {
            foreach (array_filter([$artworkPath, $previewPath]) as $path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        NewOrderCreated::dispatch(
            'custom',
            $order->order_number,
            $order->customer_name,
            $order->total_amount,
            route('admin.custom-cup-orders.show', $order),
        );

        return to_route('custom-cup-orders.confirmation')
            ->with('order_number', $order->order_number);
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

    public function confirmation(): View|RedirectResponse
    {
        if (! session()->has('order_number')) {
            return to_route('products.customize');
        }

        $order = CustomCupOrder::query()
            ->where('order_number', session('order_number'))
            ->first();

        return view('store.custom-order-confirmation', [
            'orderNumber' => session('order_number'),
            'previewUrl' => $order ? route('custom-cup-orders.preview', $order) : null,
        ]);
    }
}
