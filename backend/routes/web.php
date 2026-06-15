<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\SuperAdmin\DashboardController as SuperDashboard;
use App\Http\Controllers\Admin\SuperAdmin\OrganizationController as SuperOrgController;
use App\Http\Controllers\Admin\SuperAdmin\UserController as SuperUserController;
use App\Http\Controllers\Admin\SuperAdmin\ReportController as SuperReportController;
use App\Http\Controllers\Admin\Merchant\DashboardController as MerchantDashboard;
use App\Http\Controllers\Admin\Merchant\ProductController as MerchantProductController;
use App\Http\Controllers\Admin\Merchant\CategoryController as MerchantCategoryController;
use App\Http\Controllers\Admin\Merchant\UserController as MerchantUserController;
use App\Http\Controllers\Admin\Merchant\BranchController as MerchantBranchController;
use App\Http\Controllers\Admin\Merchant\TerminalController as MerchantTerminalController;
use App\Http\Controllers\Admin\Merchant\OrderController as MerchantOrderController;
use App\Http\Controllers\Admin\Merchant\ReportController as MerchantReportController;
use App\Http\Controllers\Admin\Merchant\SupplyOrderController as MerchantSupplyController;
use App\Http\Controllers\Admin\Merchant\CustomerController as MerchantCustomerController;
use App\Http\Controllers\Admin\Merchant\StockController as MerchantStockController;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect()->route('login');
});

// Super Admin Panel
Route::middleware(['auth', 'role:super_admin'])->prefix('super')->name('super.')->group(function () {
    Route::get('/dashboard', [SuperDashboard::class, 'index'])->name('dashboard');

    Route::resource('organizations', SuperOrgController::class);
    Route::post('organizations/{organization}/toggle-status', [SuperOrgController::class, 'toggleStatus'])->name('organizations.toggle-status');

    Route::resource('users', SuperUserController::class);
    Route::get('reports', [SuperReportController::class, 'index'])->name('reports.index');
});

// Merchant Admin Panel
Route::middleware(['auth', 'role:org_admin,super_admin'])->prefix('merchant')->name('merchant.')->group(function () {
    Route::get('/dashboard', [MerchantDashboard::class, 'index'])->name('dashboard');

    Route::resource('products', MerchantProductController::class);
    Route::post('products/{product}/toggle', [MerchantProductController::class, 'toggle'])->name('products.toggle');

    Route::resource('categories', MerchantCategoryController::class);
    Route::resource('users', MerchantUserController::class);
    Route::post('users/{user}/reset-pin', [MerchantUserController::class, 'resetPin'])->name('users.reset-pin');

    Route::resource('branches', MerchantBranchController::class);
    Route::resource('terminals', MerchantTerminalController::class);

    Route::resource('orders', MerchantOrderController::class)->only(['index', 'show']);
    Route::post('orders/{order}/return', [MerchantOrderController::class, 'returnOrder'])->name('orders.return');

    Route::get('reports/sales', [MerchantReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/products', [MerchantReportController::class, 'products'])->name('reports.products');
    Route::get('reports/stock', [MerchantReportController::class, 'stock'])->name('reports.stock');
    Route::get('reports/shifts', [MerchantReportController::class, 'shifts'])->name('reports.shifts');
    Route::get('reports/cashiers', [MerchantReportController::class, 'cashiers'])->name('reports.cashiers');

    Route::resource('supply-orders', MerchantSupplyController::class);
    Route::resource('customers', MerchantCustomerController::class);
    Route::get('stocks', [MerchantStockController::class, 'index'])->name('stocks.index');
    Route::post('stocks/adjust', [MerchantStockController::class, 'adjust'])->name('stocks.adjust');
});

// Redirect authenticated users
Route::middleware('auth')->get('/home', function () {
    $user = auth()->user();
    if ($user->isSuperAdmin()) return redirect()->route('super.dashboard');
    if ($user->isOrgAdmin()) return redirect()->route('merchant.dashboard');
    return redirect()->route('login');
})->name('home');
