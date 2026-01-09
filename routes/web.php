<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\SuperAdmin\OwnerController as SuperAdminOwnerController;
use App\Http\Controllers\Owner\OwnerController;
use App\Http\Controllers\Owner\ManagerController as OwnerManagerController;
use App\Http\Controllers\Owner\ExpenseController;
use App\Http\Controllers\Manager\ManagerController;
use App\Http\Controllers\Manager\SalesmanController as ManagerSalesmanController;
use App\Http\Controllers\Manager\ProductController as ManagerProductController;
use App\Http\Controllers\Manager\StockController as ManagerStockController;
use App\Http\Controllers\Manager\DuePaymentController;
use App\Http\Controllers\Salesman\SalesmanController;
use App\Http\Controllers\Salesman\SaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    if ($user) {
        return redirect()->route($user->getDashboardRoute());
    }

    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1'); // 5 attempts per minute
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    
    // Universal Voucher Routes (accessible by all authenticated users)
    Route::get('/voucher/{sale}/print', [VoucherController::class, 'print'])->name('voucher.print');
    Route::get('/payment-voucher/{profitRealization}', [VoucherController::class, 'paymentVoucher'])->name('payment-voucher.print');
});

// Super Admin Routes
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('owners', SuperAdminOwnerController::class);
    Route::post('/owners/{owner}/toggle-due-system', [SuperAdminOwnerController::class, 'toggleDueSystem'])->name('owners.toggle-due-system');
    Route::get('/reports', [ReportController::class, 'superAdminReports'])->name('reports');
    
    // Business Management
    Route::resource('businesses', \App\Http\Controllers\SuperAdmin\BusinessController::class);
    Route::get('/businesses/{business}/edit-template', [\App\Http\Controllers\SuperAdmin\BusinessController::class, 'editTemplate'])->name('businesses.edit-template');
    Route::put('/businesses/{business}/update-template', [\App\Http\Controllers\SuperAdmin\BusinessController::class, 'updateTemplate'])->name('businesses.update-template');
    Route::get('/businesses/{business}/add-owner', [\App\Http\Controllers\SuperAdmin\BusinessController::class, 'addOwner'])->name('businesses.add-owner');
    Route::post('/businesses/{business}/store-owner', [\App\Http\Controllers\SuperAdmin\BusinessController::class, 'storeOwner'])->name('businesses.store-owner');
    
    // Old voucher templates route (will be deprecated)
    Route::resource('voucher-templates', \App\Http\Controllers\SuperAdmin\VoucherTemplateController::class);
});

// Owner Routes
Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/due-customers', [OwnerController::class, 'dueCustomers'])->name('due-customers');
    Route::get('/all-sales', [OwnerController::class, 'allSales'])->name('all-sales');
    Route::get('/voucher/{sale}/print', [VoucherController::class, 'print'])->name('voucher.print');
    Route::get('/payment/{sale}/record', [OwnerController::class, 'recordPayment'])->name('payment.record');
    Route::post('/payment/{sale}/store', [OwnerController::class, 'storePayment'])->name('payment.store');
    Route::get('/payment-voucher/{profitRealization}', [OwnerController::class, 'paymentVoucher'])->name('payment.voucher');
    Route::resource('managers', OwnerManagerController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('products', ManagerProductController::class);
    Route::get('/stock', [ManagerStockController::class, 'index'])->name('stock.index');
    Route::post('/stock', [ManagerStockController::class, 'store'])->name('stock.store');
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
    Route::get('/reports', [ReportController::class, 'ownerReports'])->name('reports');
});

// Manager Routes
Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');
    Route::get('/due-customers', [ManagerController::class, 'dueCustomers'])->name('due-customers');
    Route::get('/payment/{sale}/record', [ManagerController::class, 'recordPayment'])->name('payment.record');
    Route::post('/payment/{sale}/store', [ManagerController::class, 'storePayment'])->name('payment.store');
    Route::get('/voucher/{sale}/print', [VoucherController::class, 'print'])->name('voucher.print');
    Route::get('/payment-voucher/{profitRealization}', [ManagerController::class, 'paymentVoucher'])->name('payment.voucher');
    Route::resource('salesmen', ManagerSalesmanController::class);
    Route::resource('products', ManagerProductController::class);
    Route::get('/stock', [ManagerStockController::class, 'index'])->name('stock.index');
    Route::post('/stock', [ManagerStockController::class, 'store'])->name('stock.store');
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/due-payments', [DuePaymentController::class, 'index'])->name('due-payments.index');
    Route::post('/due-payments/{sale}', [DuePaymentController::class, 'update'])->name('due-payments.update');
    Route::get('/reports', [ReportController::class, 'managerReports'])->name('reports');
});

// Salesman Routes
Route::middleware(['auth', 'role:salesman'])->prefix('salesman')->name('salesman.')->group(function () {
    Route::get('/dashboard', [SalesmanController::class, 'dashboard'])->name('dashboard');
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
});
