<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\TerminalController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\SupplyOrderController;
use App\Http\Controllers\Api\StockController;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/pin-login', [AuthController::class, 'pinLogin']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/barcode', [ProductController::class, 'searchByBarcode']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    // Categories
    Route::apiResource('/categories', CategoryController::class);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/return', [OrderController::class, 'returnOrder']);
    Route::get('/orders/{order}/receipt', [OrderController::class, 'receipt']);

    // Shifts
    Route::post('/shifts/open', [ShiftController::class, 'openShift']);
    Route::post('/shifts/{shift}/close', [ShiftController::class, 'closeShift']);
    Route::get('/shifts/current', [ShiftController::class, 'currentShift']);
    Route::get('/shifts/{shift}/report', [ShiftController::class, 'shiftReport']);
    Route::post('/shifts/cash-in', [ShiftController::class, 'cashIn']);
    Route::post('/shifts/cash-out', [ShiftController::class, 'cashOut']);

    // Customers
    Route::apiResource('/customers', CustomerController::class);

    // Reports
    Route::get('/reports/daily-sales', [ReportController::class, 'dailySales']);
    Route::get('/reports/period-sales', [ReportController::class, 'periodSales']);
    Route::get('/reports/top-products', [ReportController::class, 'topProducts']);
    Route::get('/reports/stock', [ReportController::class, 'stockReport']);
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard']);

    // Users
    Route::apiResource('/users', UserController::class);
    Route::post('/users/{user}/reset-pin', [UserController::class, 'resetPin']);

    // Branches
    Route::apiResource('/branches', BranchController::class);

    // Terminals
    Route::apiResource('/terminals', TerminalController::class);

    // Supply orders (Kirim)
    Route::apiResource('/supply-orders', SupplyOrderController::class);

    // Stock management
    Route::get('/stocks', [StockController::class, 'index']);
    Route::post('/stocks/adjust', [StockController::class, 'adjust']);

    // Super Admin routes
    Route::middleware('role:super_admin')->prefix('admin')->group(function () {
        Route::apiResource('/organizations', OrganizationController::class);
        Route::get('/stats', [OrganizationController::class, 'stats']);
    });
});
