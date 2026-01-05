<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/admin');
    }

    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Dashboard (AUTH ONLY, NO VERIFIED)
|--------------------------------------------------------------------------
| Email verification belum diwajibkan untuk mencegah redirect issue
| Dashboard utama ada di /admin (Filament)
*/

Route::get('/dashboard', function () {
    // Redirect to Filament admin dashboard
    return redirect('/admin');
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Filament Reports Export Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'web'])
    ->prefix('admin/reports')
    ->name('filament.admin.pages.reports.')
    ->group(function () {
        Route::get('/export-cashflow', [\App\Filament\Pages\Reports::class, 'exportCashflowPdf'])
            ->name('export-cashflow');

        Route::get('/export-budget-goal', [\App\Filament\Pages\Reports::class, 'exportBudgetGoalPdf'])
            ->name('export-budget-goal');

        Route::get('/export-daily', [\App\Filament\Pages\Reports::class, 'exportDailyPdf'])
            ->name('export-daily');
    });

/*
|--------------------------------------------------------------------------
| Auth Routes (Login, Register, Forgot Password, etc)
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';
