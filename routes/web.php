<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::public.index')->name('index.page');
Route::livewire('/login', 'pages::auth.login')->name('login');
Route::livewire('/register', 'pages::auth.register')->name('register');
Route::livewire('/owner/login', 'pages::auth.shop_owner_auth.login')->name('owner.login');
Route::livewire('/owner/register', 'pages::auth.shop_owner_auth.register')->name('owner.register');
Route::livewire('/admin/login', 'pages::auth.admin_auth.login')->name('admin.login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('customer')->middleware(['customer'])->group(function () {
    Route::livewire('/dashboard', 'pages::customer.dashboard')->name('customer.dashboard');
    Route::livewire('/profile', 'pages::customer.profile')->name('customer.profile');
    Route::livewire('/update/profile', 'pages::customer.update-profile')->name('customer.update_profile');
    Route::livewire('/product/index', 'pages::customer.product-index')->name('customer.product_index');
    Route::livewire('/product/{product}', 'pages::customer.product-details')->name('customer.product_detail');
    Route::livewire('/checkout', 'pages::customer.order-checkout')->name('customer.checkout');
    Route::livewire('/orders', 'pages::customer.order-history')->name('customer.orders');
});

Route::middleware(['auth', 'owner'])->prefix('owner')->group(function () {
    Route::livewire('/business/setup', 'pages::shop_owner.business-setup')->name('owner.business_setup');
    Route::livewire('/business/status', 'pages::shop_owner.business-status')->name('owner.business_status');
    Route::livewire('/dashboard', 'pages::shop_owner.dashboard')->name('owner.dashboard');
    Route::livewire('/profile', 'pages::shop_owner.profile')->name('owner.profile');
    Route::livewire('/update/profile', 'pages::shop_owner.update-profile')->name('owner.update_profile');
    Route::livewire('/order_management', 'pages::shop_owner.order-management')->name('owner.order_management');
    Route::livewire('/products/create', 'pages::shop_owner.create-product-post')->name('owner.product_create');
    Route::livewire('/products', 'pages::shop_owner.product-post-table')->name('owner.products');
    Route::livewire('/products/{product}/edit', 'pages::shop_owner.update-product-post')->name('owner.product_edit');
    Route::livewire('/categories', 'pages::shop_owner.category-management')->name('owner.categories');
    Route::livewire('/inventory', 'pages::shop_owner.inventory-management')->name('owner.inventory');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::livewire('/dashboard', 'pages::admin.dashboard')->name('admin.dashboard');
    Route::livewire('/profile', 'pages::admin.profile')->name('admin.profile');
    Route::livewire('/update/profile', 'pages::admin.update-profile')->name('admin.update_profile');
    Route::livewire('/shop_approval', 'pages::admin.shop-approval')->name('admin.shop_approval');
});
