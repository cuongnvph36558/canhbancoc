@extends('layouts.store')

@section('title', 'Đơn hàng '.$order->order_number)
@section('content')
    <section class="shopping-page">
        <div class="container">
            <div class="order-confirmation">
                <span class="order-confirmation-icon" aria-hidden="true">{{ $order->payment_status === \App\Models\Order::PAYMENT_PAID ? '✓' : '♡' }}</span>
                <span class="eyebrow">ĐƠN HÀNG {{ $order->order_number }}</span>
                @if (in_array($order->payment_method, [\App\Models\Order::PAYMENT_METHOD_COD, \App\Models\Order::PAYMENT_METHOD_BANK_QR], true) && $order->order_status === \App\Models\Order::STATUS_CANCELLED)
                    <h1>Đơn hàng đã <em>được hủy.</em></h1>
                    <p>Nếu bạn cần hỗ trợ, vui lòng liên hệ cửa hàng.</p>
                @elseif ($order->payment_method === \App\Models\Order::PAYMENT_METHOD_COD && $order->payment_status !== \App\Models\Order::PAYMENT_PAID)
                    <h1>Đơn hàng đã <em>được tiếp nhận.</em></h1>
                    <p>Đơn hàng đang được chuẩn bị. Bạn sẽ thanh toán khi nhận hàng.</p>
                @elseif ($order->payment_status === \App\Models\Order::PAYMENT_PAID)
                    <h1>Cảm ơn bạn đã <em>đặt hàng.</em></h1>
                    @if ($order->order_status === \App\Models\Order::STATUS_NEEDS_REVIEW)
                        <p>Thanh toán đã được ghi nhận. Cửa hàng sẽ liên hệ bạn để xác nhận tình trạng giao hàng.</p>
                    @else
                        <p>Thanh toán thành công. Chúng mình sẽ sớm chuẩn bị món quà của bạn.</p>
                    @endif
                @elseif ($order->payment_status === \App\Models\Order::PAYMENT_FAILED)
                    <h1>Thanh toán <em>chưa hoàn tất.</em></h1>
                    <p>Đơn hàng chưa được thanh toán. Hãy liên hệ cửa hàng nếu bạn cần hỗ trợ.</p>
                @elseif ($order->payment_method === \App\Models\Order::PAYMENT_METHOD_BANK_QR)
                    <h1>Vui lòng <em>chuyển khoản.</em></h1>
                    <p>Đơn hàng đã được tiếp nhận. Cửa hàng sẽ xác nhận thủ công sau khi nhận được tiền.</p>
                @else
                    <h1>Đang xác nhận <em>thanh toán.</em></h1>
                    <p>VNPay đang gửi kết quả thanh toán. Trạng thái đơn hàng sẽ được cập nhật tự động.</p>
                @endif
                <div class="order-confirmation-summary">
                    <div><span>Địa chỉ giao hàng</span><strong>{{ collect([$order->shipping_address, $order->shipping_ward, $order->shipping_province])->filter()->implode(', ') }}</strong></div>
                    <div>
                        <span>Phương thức thanh toán</span>
                        <strong>
                            @if ($order->payment_method === \App\Models\Order::PAYMENT_METHOD_COD)
                                Thanh toán khi nhận hàng (COD)
                            @elseif ($order->payment_method === \App\Models\Order::PAYMENT_METHOD_BANK_QR)
                                Chuyển khoản ngân hàng qua QR
                            @else
                                VNPay
                            @endif
                        </strong>
                    </div>
                    @if (
                        $order->payment_method === \App\Models\Order::PAYMENT_METHOD_BANK_QR
                        && $order->payment_status === \App\Models\Order::PAYMENT_PENDING
                        && $order->order_status !== \App\Models\Order::STATUS_CANCELLED
                        && ($bankQrImageUrl || $order->payment_qr_image_path)
                    )
                        <div class="order-payment-qr">
                            <span>Quét mã QR để chuyển khoản</span>
                            <img
                                src="{{ $bankQrImageUrl ?? \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_qr_image_path) }}"
                                alt="Mã QR thanh toán ngân hàng"
                            >
                            <strong>{{ $order->payment_bank_name }} · {{ $order->payment_account_number }}</strong>
                            <span>Chủ tài khoản: {{ $order->payment_account_name }}</span>
                            <span>QR đã điền sẵn {{ number_format((float) $order->total, 0, ',', '.') }}đ và nội dung chuyển khoản {{ $order->order_number }}.</span>
                        </div>
                    @endif
                    @foreach ($order->items as $item)
                        <div><span>{{ $item->product_name }} × {{ $item->quantity }}</span><strong>{{ number_format((float) $item->line_total, 0, ',', '.') }}đ</strong></div>
                    @endforeach
                    <div><span>Giao hàng</span><strong>{{ (float) $order->shipping_fee === 0.0 ? 'Miễn phí' : number_format((float) $order->shipping_fee, 0, ',', '.').'đ' }}</strong></div>
                    <div class="summary-total"><span>Tổng cộng</span><strong>{{ number_format((float) $order->total, 0, ',', '.') }}đ</strong></div>
                </div>
                <a class="shopping-primary-button" href="{{ route('products.index') }}">Tiếp tục khám phá</a>
            </div>
        </div>
    </section>
@endsection
