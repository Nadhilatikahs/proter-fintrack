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

Route::middleware(['auth'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/reports/export/{type}', [ReportExportController::class, 'export'])
            ->name('reports.export');
    });

// routes/web.php
Route::get('/reports', [ReportController::class, 'index'])
    ->middleware('auth')
    ->name('reports');

use App\Http\Controllers\ReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
});

Route::get('/reports/goal/pdf', [ReportController::class, 'exportGoalPdf'])
    ->middleware('auth')
    ->name('reports.goal.pdf');

Route::get('/reports/pdf/full', [ReportController::class, 'exportFullPdf'])
    ->middleware('auth')
    ->name('reports.pdf.full');

use App\Filament\Pages\TransactionsOverview;

Route::get('/admin/transactions/budget/{budget}', function (\App\Models\BudgetGoal $budget) {
    abort_if($budget->type !== 'budget', 404);

    return redirect()->route('filament.admin.pages.transactions-overview', [
        'filter' => 'budget',
        'budget_goal_id' => $budget->id,
    ]);
})->middleware(['auth']);

use App\Filament\Pages\Reports;

Route::get(
    '/admin/reports/export-cashflow',
    [Reports::class, 'exportCashflowPdf']
)->name('filament.admin.pages.reports.export-cashflow');

Route::get(
    '/admin/reports/export-budget-goal',
    [Reports::class, 'exportBudgetGoalPdf']
)->name('filament.admin.pages.reports.export-budget-goal');


Route::get(
    '/admin/reports/export-daily',
    [Reports::class, 'exportDailyPdf']
)->name('filament.admin.pages.reports.export-daily');

Route::view('/intro-1', 'auth.intro-1')->name('intro.1');
Route::view('/intro-2', 'auth.intro-2')->name('intro.2');
Route::view('/auth-choice', 'auth.choice')->name('auth.choice');

Route::get('/password-success', fn () => view('auth.password-success'))
    ->name('password.success');

Route::view('/password-success', 'auth.password-success')
    ->name('password.success');

Route::get('/', function () {
    return view('auth.intro-1');
});

require __DIR__.'/auth.php';
