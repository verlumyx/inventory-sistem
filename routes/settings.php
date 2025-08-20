<?php

use App\Http\Controllers\Settings\CompanyController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorController;
use App\Inventory\ExchangeRate\Controllers\ExchangeRateController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/two-factor', [TwoFactorController::class, 'edit'])->name('two-factor.edit');
    Route::post('settings/two-factor', [TwoFactorController::class, 'store'])->name('two-factor.store');
    Route::post('settings/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::delete('settings/two-factor', [TwoFactorController::class, 'destroy'])->name('two-factor.destroy');
    Route::post('settings/two-factor/recovery-codes', [TwoFactorController::class, 'recoveryCodes'])->name('two-factor.recovery-codes');
    Route::post('settings/two-factor/show-recovery-codes', [TwoFactorController::class, 'showRecoveryCodes'])->name('two-factor.show-recovery-codes');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/appearance');
    })->name('appearance');

    // Company Configuration Routes
    Route::get('settings/company', [CompanyController::class, 'edit'])->name('settings.company');
    Route::put('settings/company', [CompanyController::class, 'update'])->name('settings.company.update');

    // Exchange Rate Configuration Routes
    Route::get('settings/exchange-rate', [ExchangeRateController::class, 'index'])->name('settings.exchange-rate');
    Route::put('settings/exchange-rate', [ExchangeRateController::class, 'update'])->name('settings.exchange-rate.update');
});
