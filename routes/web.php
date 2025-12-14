<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Filament Reports export routes
Route::middleware(['auth', 'web'])->prefix('admin/reports')->name('filament.admin.pages.reports.')->group(function () {
    Route::get('/export-cashflow', [\App\Filament\Pages\Reports::class, 'exportCashflowPdf'])->name('export-cashflow');
    Route::get('/export-budget-goal', [\App\Filament\Pages\Reports::class, 'exportBudgetGoalPdf'])->name('export-budget-goal');
    Route::get('/export-daily', [\App\Filament\Pages\Reports::class, 'exportDailyPdf'])->name('export-daily');
});

require __DIR__ . '/auth.php';
