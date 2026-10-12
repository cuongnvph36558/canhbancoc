<?php

use App\Http\Controllers\AdminContactInquiryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCustomCupOrderController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminPageContentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomCupOrderController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SupportChatController;
use App\Http\Controllers\VNPayCallbackController;
use Illuminate\Support\Facades\Route;

Route::get('/product-images/{productImage}', ProductImageController::class)
    ->whereNumber('productImage')
    ->name('product-images.show');

Route::get('/', [StoreController::class, 'home'])->name('home');
Route::get('/san-pham', [StoreController::class, 'products'])->name('products.index');
Route::view('/coc-tu-custom', 'store.customizer')->name('products.customize');
Route::post('/dat-hang/coc-tu-custom', [CustomCupOrderController::class, 'store'])->name('custom-cup-orders.store');
Route::get('/dat-hang/coc-tu-custom/{order}/preview', [CustomCupOrderController::class, 'preview'])->name('custom-cup-orders.preview');
Route::get('/dat-hang/hoan-tat', [CustomCupOrderController::class, 'confirmation'])->name('custom-cup-orders.confirmation');
Route::get('/san-pham/{slug}', [StoreController::class, 'product'])->name('products.show');
Route::get('/gioi-thieu', [StoreController::class, 'about'])->name('about');
Route::get('/lien-he', [StoreController::class, 'contact'])->name('contact');
Route::post('/lien-he', [StoreController::class, 'submitContact'])->name('contact.submit');
Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::post('/gio-hang', [CartController::class, 'store'])->name('cart.store');
Route::put('/gio-hang/{productId}', [CartController::class, 'update'])->whereNumber('productId')->name('cart.update');
Route::delete('/gio-hang/{productId}', [CartController::class, 'destroy'])->whereNumber('productId')->name('cart.destroy');
Route::get('/thanh-toan/vnpay/return', [VNPayCallbackController::class, 'returned'])->name('payments.vnpay.return');
Route::post('/thanh-toan/vnpay/ipn', [VNPayCallbackController::class, 'ipn'])->name('payments.vnpay.ipn');

Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->name('login.store');
    Route::get('/dang-ky', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');
    Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/don-hang-cua-toi', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
    Route::get('/don-hang-coc-custom/{customCupOrder}', [CustomerOrderController::class, 'showCustomOrder'])->name('customer.custom-cup-orders.show');
    Route::get('/don-hang-coc-custom/{customCupOrder}/preview', [CustomerOrderController::class, 'customOrderPreview'])->name('customer.custom-cup-orders.preview');
    Route::get('/don-hang/{orderNumber}', [CheckoutController::class, 'show'])->name('orders.show');
    Route::get('/tin-nhan', [SupportChatController::class, 'index'])->name('chat.index');
    Route::get('/tin-nhan/cap-nhat', [SupportChatController::class, 'messages'])->name('chat.messages.index');
    Route::post('/tin-nhan', [SupportChatController::class, 'store'])->middleware('throttle:30,1')->name('chat.messages.store');

    Route::middleware('admin')->group(function () {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/lien-he', [AdminContactInquiryController::class, 'index'])->name('admin.contact-inquiries.index');
        Route::get('/admin/lien-he/{inquiry}', [AdminContactInquiryController::class, 'show'])->name('admin.contact-inquiries.show');
        Route::patch('/admin/lien-he/{inquiry}/trang-thai', [AdminContactInquiryController::class, 'updateStatus'])->name('admin.contact-inquiries.update-status');
        Route::get('/admin/tin-nhan', [SupportChatController::class, 'adminIndex'])->name('admin.chat.index');
        Route::get('/admin/tin-nhan/{supportConversation}', [SupportChatController::class, 'adminShow'])->name('admin.chat.show');
        Route::get('/admin/tin-nhan/{supportConversation}/cap-nhat', [SupportChatController::class, 'adminMessages'])->name('admin.chat.messages.index');
        Route::post('/admin/tin-nhan/{supportConversation}', [SupportChatController::class, 'adminStore'])->middleware('throttle:30,1')->name('admin.chat.messages.store');
        Route::get('/admin/noi-dung/{page}', [AdminPageContentController::class, 'edit'])->name('admin.pages.edit');
        Route::put('/admin/noi-dung/{page}', [AdminPageContentController::class, 'update'])->name('admin.pages.update');
        Route::get('/admin/nguoi-dung', [AdminController::class, 'index'])->name('admin.users');
        Route::put('/admin/nguoi-dung/{user}/role', [AdminController::class, 'updateRole'])->name('admin.users.update-role');
        Route::post('/admin/nguoi-dung/{user}/toggle-status', [AdminController::class, 'toggleStatus'])->name('admin.users.toggle-status');
        Route::delete('/admin/nguoi-dung/{user}', [AdminController::class, 'destroy'])->name('admin.users.destroy');

        Route::get('/admin/don-hang', [AdminOrderController::class, 'index'])->name('admin.orders.index');
        Route::get('/admin/don-hang/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
        Route::patch('/admin/don-hang/{order}/trang-thai', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.update-status');
        Route::patch('/admin/don-hang/{order}/xac-nhan-thanh-toan', [AdminOrderController::class, 'confirmBankQrPayment'])->name('admin.orders.confirm-bank-qr-payment');

        Route::get('/admin/don-hang-coc-tu-custom', [AdminCustomCupOrderController::class, 'index'])->name('admin.custom-cup-orders.index');
        Route::get('/admin/don-hang-coc-tu-custom/{order}', [AdminCustomCupOrderController::class, 'show'])->name('admin.custom-cup-orders.show');
        Route::patch('/admin/don-hang-coc-tu-custom/{order}/trang-thai', [AdminCustomCupOrderController::class, 'updateStatus'])->name('admin.custom-cup-orders.update-status');
        Route::get('/admin/don-hang-coc-tu-custom/{order}/preview', [AdminCustomCupOrderController::class, 'preview'])->name('admin.custom-cup-orders.preview');
        Route::get('/admin/don-hang-coc-tu-custom/{order}/hinh-in', [AdminCustomCupOrderController::class, 'downloadArtwork'])->name('admin.custom-cup-orders.artwork');

        Route::get('/admin/san-pham', [ProductController::class, 'index'])->name('admin.products.index');
        Route::get('/admin/san-pham/tao-moi', [ProductController::class, 'create'])->name('admin.products.create');
        Route::post('/admin/san-pham', [ProductController::class, 'store'])->name('admin.products.store');
        Route::get('/admin/san-pham/{product}', [ProductController::class, 'show'])->name('admin.products.show');
        Route::get('/admin/san-pham/{product}/chinh-sua', [ProductController::class, 'edit'])->name('admin.products.edit');
        Route::put('/admin/san-pham/{product}', [ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/admin/san-pham/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
        Route::post('/admin/san-pham/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('admin.products.toggle-status');
    });
});
