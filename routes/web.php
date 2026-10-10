<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::public.index')->name('index.page');
Route::livewire('/login', 'pages::auth.login')->name('login');

Route::livewire('/register', 'pages::auth.register')->name('register');
Route::livewire('/owner/register', 'pages::auth.shop_owner_auth.register')->name('owner.register');

Route::livewire('/forgot-password', 'pages::auth.forgot-password')->middleware('guest')->name('password.request');
Route::livewire('/reset-password/{token}', 'pages::auth.reset-password')->middleware('guest')->name('password.reset');

Route::livewire('/privacy-policy', 'pages::public.privacy-policy')->name('privacy_policy');
Route::livewire('/terms-of-service', 'pages::public.terms-of-service')->name('terms_of_service');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('customer')->middleware(['customer'])->group(function () {
    Route::livewire('/dashboard', 'pages::customer.dashboard')->name('customer.dashboard');

    Route::livewire('/profile', 'pages::customer.profile')->name('customer.profile');
    Route::livewire('/update/profile', 'pages::customer.update-profile')->name('customer.update_profile');

    Route::livewire('/product/{product}', 'pages::customer.product-details')->name('customer.product_detail');
    Route::livewire('/checkout', 'pages::customer.order-checkout')->name('customer.checkout');
    Route::livewire('/orders', 'pages::customer.order-history')->name('customer.orders');
    Route::livewire('/new-arrivals', 'pages::customer.new-arrival')->name('customer.new_arrivals');

    Route::livewire('/services', 'pages::customer.services')->name('customer.services.index');
    Route::livewire('/services/{post}/book', 'pages::customer.service-detail')->name('customer.services.book');

    Route::livewire('/appointments', 'pages::customer.appointment-history')->name('customer.appointments');
    Route::livewire('/appointments/{appointment}', 'pages::customer.appointment-detail')->name('customer.appointment_detail');

    Route::livewire('/map', 'pages::customer.map.live')->name('customer.map');

});

Route::middleware(['auth', 'owner'])->prefix('owner')->group(function () {
    Route::livewire('/business/setup', 'pages::shop_owner.business-setup')->name('owner.business_setup');
    Route::livewire('/business/status', 'pages::shop_owner.business-status')->name('owner.business_status');
    Route::livewire('/shop/settings', 'pages::shop_owner.shop-settings')->name('owner.shop.settings');
    
    Route::livewire('/switch-shop/{tenant}', 'pages::shop_owner.switch-shop')->name('owner.switch_shop');  
    Route::livewire('/business/create', 'pages::shop_owner.business-create')->name('owner.business_create');  

    Route::livewire('/dashboard', 'pages::shop_owner.dashboard')->name('owner.dashboard');
    Route::livewire('/profile', 'pages::shop_owner.profile')->name('owner.profile');
    Route::livewire('/update/profile', 'pages::shop_owner.update-profile')->name('owner.update_profile');

    Route::livewire('/order_management', 'pages::shop_owner.order-management')->name('owner.order_management');
    Route::livewire('/products/create', 'pages::shop_owner.create-product-post')->name('owner.product_create');
    Route::livewire('/products', 'pages::shop_owner.product-post-table')->name('owner.products');
    Route::livewire('/products/{product}/edit', 'pages::shop_owner.update-product-post')->name('owner.product_edit');

    Route::livewire('/appointments', 'pages::shop_owner.appointment-management')->name('owner.appointments');
    Route::livewire('/roadside', 'pages::shop_owner.roadside')->name('owner.roadside');

    Route::livewire('/categories', 'pages::shop_owner.category-management')->name('owner.categories');
    Route::livewire('/inventory', 'pages::shop_owner.inventory-management')->name('owner.inventory');

    Route::livewire('/brands', 'pages::shop_owner.brands')->name('owner.brands');

    Route::livewire('/reports', 'pages::shop_owner.reports')->name('owner.reports');

    Route::livewire('/employees', 'pages::shop_owner.employees')->name('owner.employees');
    Route::livewire('/employees/{employee}/schedule', 'pages::shop_owner.employee-schedule')->name('owner.employees.schedule');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::livewire('/dashboard', 'pages::admin.dashboard')->name('admin.dashboard');

    Route::livewire('/users', 'pages::admin.users')->name('admin.users');
    Route::livewire('/reports', 'pages::admin.reports')->name('admin.reports');
    Route::livewire('/orders', 'pages::admin.orders')->name('admin.orders');
    Route::livewire('/reviews', 'pages::admin.reviews')->name('admin.reviews');

    Route::livewire('/profile', 'pages::admin.profile')->name('admin.profile');
    Route::livewire('/update/profile', 'pages::admin.update-profile')->name('admin.update_profile');

    Route::livewire('/shop_approval', 'pages::admin.shop-approval')->name('admin.shop_approval');

    Route::livewire('/shops', 'pages::admin.approved-shop-table')->name('admin.shops.index');
});


Broadcast::routes();
