<?php

use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'home'])->name('home');
Route::get('/san-pham', [StoreController::class, 'products'])->name('products.index');
Route::get('/san-pham/{slug}', [StoreController::class, 'product'])->name('products.show');
Route::get('/gioi-thieu', [StoreController::class, 'about'])->name('about');
Route::get('/lien-he', [StoreController::class, 'contact'])->name('contact');
Route::post('/lien-he', [StoreController::class, 'submitContact'])->name('contact.submit');
