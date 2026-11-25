<?php

declare(strict_types=1);

use App\Http\Controllers\Account\NewPasswordController;
use App\Http\Controllers\Account\PasswordResetLinkController;
use App\Http\Controllers\Account\RegistrationController;
use App\Http\Controllers\Account\SessionController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\ShoppingListItemController;
use App\Providers\AccountServiceProvider;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Accounts (B5): sign-up, sign-in and password recovery, for guests only.
Route::middleware('guest')->group(static function (): void {
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store']);
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.update');
});

// The signed-in user's own list. No route takes a user id: the owner is always the session's.
Route::middleware('auth')->group(static function (): void {
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
    Route::get('/list', [ShoppingListController::class, 'show'])->name('shopping-list');
    Route::delete('/list', [ShoppingListController::class, 'clear'])->name('shopping-list.clear');
    Route::post('/list/items', [ShoppingListItemController::class, 'store'])->name('shopping-list.items.store');
    Route::delete('/list/items/{product}', [ShoppingListItemController::class, 'destroy'])
        ->whereNumber('product')
        ->name('shopping-list.items.destroy');
});

// Store management, for administrators only.
Route::middleware(['auth', 'can:'.AccountServiceProvider::MANAGE_STORES])
    ->prefix('admin')
    ->group(static function (): void {
        Route::get('/stores', [StoreController::class, 'index'])->name('admin.stores');
    });
