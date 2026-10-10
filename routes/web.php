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
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\SouvenirController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminPanelController;

// ========== LOGIN (tanpa middleware auth) ==========
Route::get('login', fn () => redirect()->route('dashboard'))->name('login');
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
    Route::get('onboarding', fn () => redirect()->route('dashboard'))->name('onboarding.index');
    Route::post('onboarding/finish', [OnboardingController::class, 'finish'])->name('onboarding.finish');
});

// Dashboard / public entry point: guest=login, new user=onboarding, user with
// wedding=dashboard. Keeping it outside auth avoids /login and /onboarding in URL.
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// ========== SEMUA HALAMAN UTAMA (wajib login) ==========
Route::middleware('auth')->group(function () {

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

    // Module 12 - Tabungan Pernikahan
    Route::get('savings', [SavingsController::class, 'index'])->name('savings.index');
    Route::post('savings', [SavingsController::class, 'store'])->name('savings.store');
    Route::put('savings/{savingsGoal}', [SavingsController::class, 'update'])->name('savings.update');
    Route::delete('savings/{savingsGoal}', [SavingsController::class, 'destroy'])->name('savings.destroy');
    Route::post('savings/{savingsGoal}/transactions', [SavingsController::class, 'storeTransaction'])->name('savings.transactions.store');
    Route::delete('savings/{savingsGoal}/transactions/{transaction}', [SavingsController::class, 'destroyTransaction'])->name('savings.transactions.destroy');

    // Module 13 - Lamaran
    Route::get('proposals', [ProposalController::class, 'index'])->name('proposals.index');
    Route::put('proposals/event', [ProposalController::class, 'updateEvent'])->name('proposals.event.update');

    Route::post('proposals/checklists', [ProposalController::class, 'storeChecklist'])->name('proposals.checklists.store');
    Route::put('proposals/checklists/{checklist}', [ProposalController::class, 'updateChecklist'])->name('proposals.checklists.update');
    Route::delete('proposals/checklists/{checklist}', [ProposalController::class, 'destroyChecklist'])->name('proposals.checklists.destroy');

    Route::post('proposals/guests', [ProposalController::class, 'storeGuest'])->name('proposals.guests.store');
    Route::put('proposals/guests/{guest}', [ProposalController::class, 'updateGuest'])->name('proposals.guests.update');
    Route::delete('proposals/guests/{guest}', [ProposalController::class, 'destroyGuest'])->name('proposals.guests.destroy');

    Route::post('proposals/budgets', [ProposalController::class, 'storeBudget'])->name('proposals.budgets.store');
    Route::put('proposals/budgets/{budget}', [ProposalController::class, 'updateBudget'])->name('proposals.budgets.update');
    Route::delete('proposals/budgets/{budget}', [ProposalController::class, 'destroyBudget'])->name('proposals.budgets.destroy');

    Route::post('proposals/vendors/{vendor}/toggle', [ProposalController::class, 'toggleVendor'])->name('proposals.vendors.toggle');

    // Module 14 - Seserahan
    Route::get('souvenirs', [SouvenirController::class, 'index'])->name('souvenirs.index');
    Route::post('souvenirs', [SouvenirController::class, 'store'])->name('souvenirs.store');
    Route::put('souvenirs/{souvenir}', [SouvenirController::class, 'update'])->name('souvenirs.update');
    Route::delete('souvenirs/{souvenir}', [SouvenirController::class, 'destroy'])->name('souvenirs.destroy');

    // Module 15 - Persyaratan Nikah
    Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::put('documents/{requirement}', [DocumentController::class, 'update'])->name('documents.update');
    Route::delete('documents/{requirement}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('documents/{requirement}/history', [DocumentController::class, 'history'])->name('documents.history');
    Route::post('documents/templates/{template}', [DocumentController::class, 'applyTemplate'])->name('documents.templates.apply');

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

        // Module 15 - Template Persyaratan Nikah (dikelola superadmin).
        Route::get('admin/document-templates', [DocumentTemplateController::class, 'index'])->name('admin.document-templates.index');
        Route::post('admin/document-templates', [DocumentTemplateController::class, 'store'])->name('admin.document-templates.store');
        Route::put('admin/document-templates/{template}', [DocumentTemplateController::class, 'update'])->name('admin.document-templates.update');
        Route::delete('admin/document-templates/{template}', [DocumentTemplateController::class, 'destroy'])->name('admin.document-templates.destroy');
    });
});
