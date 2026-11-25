<?php

declare(strict_types=1);

use App\Http\Controllers\Account\NewPasswordController;
use App\Http\Controllers\Account\PasswordResetLinkController;
use App\Http\Controllers\Account\RegistrationController;
use App\Http\Controllers\Account\SessionController;
use App\Http\Controllers\AcquiredItemController;
use App\Http\Controllers\Admin\AssortmentController;
use App\Http\Controllers\Admin\FloorPlanController;
use App\Http\Controllers\Admin\NewStoreController;
use App\Http\Controllers\Admin\PartitionController;
use App\Http\Controllers\Admin\RouteGenerationController;
use App\Http\Controllers\Admin\SectorController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\StoreGridController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\ShoppingListItemController;
use App\Http\Controllers\StoreSelectionController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\VisitMapController;
use App\Providers\AccountServiceProvider;
use Illuminate\Support\Facades\Route;

// An id in a URL is a positive integer, so the identifiers of the domain accept it.
Route::pattern('store', '[1-9][0-9]{0,18}');
Route::pattern('product', '[1-9][0-9]{0,18}');
Route::pattern('sector', '[1-9][0-9]{0,18}');
Route::pattern('partition', '[1-9][0-9]{0,18}');

Route::view('/', 'home')->name('home');

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
        ->name('shopping-list.items.destroy');

    // B6: choose a store, follow the visit, see it on the map.
    Route::get('/stores', [StoreSelectionController::class, 'index'])->name('stores');
    Route::get('/stores/{store}/visit', [VisitController::class, 'show'])->name('visit');
    Route::get('/stores/{store}/map', [VisitMapController::class, 'show'])->name('visit.map');
    Route::post('/stores/{store}/visit/items/{product}', [AcquiredItemController::class, 'store'])
        ->name('visit.acquire');
});

// Store management, for administrators only.
Route::middleware(['auth', 'can:'.AccountServiceProvider::MANAGE_STORES])
    ->prefix('admin')
    ->group(static function (): void {
        Route::get('/stores', [StoreController::class, 'index'])->name('admin.stores');
        Route::get('/stores/create', [NewStoreController::class, 'create'])->name('admin.stores.create');
        Route::post('/stores', [NewStoreController::class, 'store'])->name('admin.stores.store');
        Route::get('/stores/{store}', [StoreController::class, 'show'])->name('admin.stores.show');
        Route::get('/stores/{store}/grid.png', [StoreGridController::class, 'show'])->name('admin.stores.grid');
        Route::post('/stores/{store}/floor-plan', [FloorPlanController::class, 'update'])->name('admin.stores.floor-plan');
        Route::post('/stores/{store}/sectors', [SectorController::class, 'store'])->name('admin.stores.sectors.store');
        Route::put('/stores/{store}/sectors/{sector}', [SectorController::class, 'update'])->name('admin.stores.sectors.update');
        Route::delete('/stores/{store}/sectors/{sector}', [SectorController::class, 'destroy'])->name('admin.stores.sectors.destroy');
        Route::post('/stores/{store}/partitions', [PartitionController::class, 'store'])->name('admin.stores.partitions.store');
        Route::delete('/stores/{store}/partitions/{partition}', [PartitionController::class, 'destroy'])
            ->name('admin.stores.partitions.destroy');
        Route::get('/stores/{store}/assortment', [AssortmentController::class, 'edit'])->name('admin.stores.assortment');
        Route::put('/stores/{store}/assortment', [AssortmentController::class, 'update']);
        Route::post('/stores/{store}/routes', [RouteGenerationController::class, 'store'])->name('admin.stores.routes');
    });
