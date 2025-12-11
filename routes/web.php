<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FintrackDashboardController;
use App\Http\Controllers\ReportExportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Kalau mau alias lain:
    // Route::get('/fintrack/dashboard', [DashboardController::class, 'index'])
    //     ->name('fintrack.dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [FintrackDashboardController::class, 'index'])
        ->name('dashboard');
});


Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/reports/export/{type}', [ReportExportController::class, 'export'])
        ->name('admin.reports.export');
});

Route::get('/admin/leave', function (Request $request) {
    // Logout user
    Auth::logout();

    // Invalidate session & regenerate CSRF token
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Redirect ke halaman login Filament admin
    return redirect()->route('filament.admin.auth.login');
})->name('admin.leave');

Route::get('/admin/budget-goals-alias', function () {
    return redirect()->route('filament.admin.resources.budget-goals.index');
})->name('filament.admin.pages.budget-goals');

Route::middleware(['auth'])
    ->get('/admin/reports/export', [ReportExportController::class, 'export'])
    ->name('admin.reports.export');
require __DIR__.'/auth.php';
