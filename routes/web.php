<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\MoodboardController;
use App\Http\Controllers\RundownController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminPanelController;

// ========== LOGIN (tanpa middleware auth) ==========
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// ========== SEMUA HALAMAN UTAMA (wajib login) ==========
Route::middleware('auth')->group(function () {

    // Dashboard Module
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Module 2 - Wedding Checklist
    Route::resource('checklists', ChecklistController::class)->except(['create', 'edit', 'show']);
    Route::post('checklists/{checklist}/toggle', [ChecklistController::class, 'toggleStatus'])->name('checklists.toggle');
    Route::post('checklists/{checklist}/duplicate', [ChecklistController::class, 'duplicate'])->name('checklists.duplicate');

    // Module 3 - Budget Planner
    Route::resource('budgets', BudgetController::class)->except(['create', 'edit', 'show']);

    // Module 4 - Vendor Management
    Route::resource('vendors', VendorController::class)->except(['create', 'edit', 'show']);
    Route::post('vendors/{vendor}/package-items', [VendorController::class, 'storePackageItem'])->name('vendors.package-items.store');
    Route::delete('vendors/package-items/{item}', [VendorController::class, 'destroyPackageItem'])->name('vendors.package-items.destroy');

    // Module 5 - Guest Management
    Route::resource('guests', GuestController::class)->except(['create', 'edit', 'show']);
    Route::get('guests/export/csv', [GuestController::class, 'exportCsv'])->name('guests.export');
    Route::get('guests/export/labels', [GuestController::class, 'exportLabels'])->name('guests.labels');
    Route::get('guests/{guest}/wa', [GuestController::class, 'sendWa'])->name('guests.wa');
    Route::get('guests/wa-all', [GuestController::class, 'sendWaAll'])->name('guests.wa-all');

    // Module 6 - Moodboard
    Route::resource('moodboards', MoodboardController::class)->except(['create', 'edit', 'show']);

    // Module 7 - Event Rundown
    Route::resource('rundowns', RundownController::class)->except(['create', 'edit', 'show']);

    // Module 8 - Vendor Contract
    Route::resource('contracts', ContractController::class)->except(['create', 'edit', 'show']);

    // Module 9 - Payment Tracker
    Route::resource('payments', PaymentController::class)->except(['create', 'edit', 'show']);

    // Module 10 - Gift Management
    Route::resource('gifts', GiftController::class)->except(['create', 'edit', 'show']);
    Route::post('gifts/{gift}/toggle-thank-you', [GiftController::class, 'toggleThankYou'])->name('gifts.toggle-thank-you');
    Route::post('gifts/{gift}/toggle-thankyou', [GiftController::class, 'toggleThankYou'])->name('gifts.toggle-thankyou');
    Route::get('gifts/export/csv', [GiftController::class, 'exportCsv'])->name('gifts.export');

    // Module 11 - Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Module 12 - Admin Panel
    Route::get('admin', [AdminPanelController::class, 'index'])->name('admin.index');
    Route::put('admin/wedding/{wedding}', [AdminPanelController::class, 'updateWedding'])->name('admin.wedding.update');
    Route::post('admin/dropdowns', [AdminPanelController::class, 'store'])->name('admin.dropdowns.store');
    Route::put('admin/dropdowns/{dropdownOption}', [AdminPanelController::class, 'update'])->name('admin.dropdowns.update');
    Route::delete('admin/dropdowns/{dropdownOption}', [AdminPanelController::class, 'destroy'])->name('admin.dropdowns.destroy');
});
