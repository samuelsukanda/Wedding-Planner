<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
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

// ========== REGISTRASI ==========
Route::get('register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

// ========== LOGIN VIA GOOGLE ==========
Route::get('auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

// ========== ONBOARDING (wajib login, belum punya data pernikahan) ==========
// Seluruh langkah wizard berjalan di client (Alpine), jadi hanya ada dua
// route: buka halaman wizard dan simpan hasil akhirnya.
Route::middleware('auth')->group(function () {
    Route::get('onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('onboarding/finish', [OnboardingController::class, 'finish'])->name('onboarding.finish');
});

// ========== SEMUA HALAMAN UTAMA (wajib login) ==========
Route::middleware('auth')->group(function () {

// Dashboard Module
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Foto profil. Halamannya sendiri reusing "Akun - Profile" (admin.index),
// jadi tidak ada halaman profile terpisah.
Route::get('profile', fn () => redirect()->route('admin.index'));
    Route::post('profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('profile/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');

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
    Route::post('guests/{guest}/wa-sent', [GuestController::class, 'toggleWaSent'])->name('guests.wa-sent');

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

// Module 12 - Profile & Admin Panel
    Route::get('admin', [AdminPanelController::class, 'index'])->name('admin.index');
    Route::put('admin/wedding/{wedding}', [AdminPanelController::class, 'updateWedding'])->name('admin.wedding.update');

    // Master Data (dropdown options) + Admin Panel (user management).
    // Keduanya data tingkat sistem, hanya superadmin yang boleh kelola.
    Route::middleware('superadmin')->group(function () {
        Route::get('admin/master-data', [AdminPanelController::class, 'masterData'])->name('admin.master-data.index');
        Route::post('admin/master-data', [AdminPanelController::class, 'store'])->name('admin.master-data.store');
        Route::put('admin/master-data/{dropdownOption}', [AdminPanelController::class, 'update'])->name('admin.master-data.update');
        Route::delete('admin/master-data/{dropdownOption}', [AdminPanelController::class, 'destroy'])->name('admin.master-data.destroy');

        Route::get('admin/users', [AdminPanelController::class, 'users'])->name('admin.users.index');
        Route::post('admin/users', [AdminPanelController::class, 'storeUser'])->name('admin.users.store');
        Route::put('admin/users/{user}', [AdminPanelController::class, 'updateUser'])->name('admin.users.update');
        Route::delete('admin/users/{user}', [AdminPanelController::class, 'destroyUser'])->name('admin.users.destroy');
    });
});
