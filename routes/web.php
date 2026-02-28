<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/reports', [App\Http\Controllers\DashboardController::class, 'reports'])->name('reports');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes
    Route::middleware('super_admin')->group(function () {
        Route::resource('admin/users', \App\Http\Controllers\Admin\UserController::class)->names('admin.users');
        Route::get('admin/structure', [\App\Http\Controllers\Admin\ProductStructureController::class, 'index'])->name('admin.structure');
    });

    Route::get('products/search', [\App\Http\Controllers\ProductController::class, 'search'])->name('products.search');
    Route::resource('products', \App\Http\Controllers\ProductController::class);

    Route::resource('inventory', \App\Http\Controllers\InventoryController::class);
    
    Route::get('transactions/{transaction}/invoice', [\App\Http\Controllers\TransactionController::class, 'downloadInvoice'])->name('transactions.invoice');
    Route::resource('transactions', \App\Http\Controllers\TransactionController::class);
    
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
});

require __DIR__.'/auth.php';
