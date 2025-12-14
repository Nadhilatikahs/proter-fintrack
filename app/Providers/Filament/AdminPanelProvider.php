<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard as CustomDashboard;
use App\Filament\Pages\BudgetGoals;
use App\Filament\Pages\TransactionsOverview;
use App\Filament\Pages\CategoriesOverview;
use App\Filament\Widgets\BudgetStatusWidget;
use App\Filament\Widgets\MonthlyFinanceOverview;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()

            // 🎨 Theme via Vite (BENAR untuk Filament v3)
            ->viteTheme('resources/css/filament-fintrack.css')

            ->colors([
                'primary' => Color::Amber,
            ])

            // ========================
            // RESOURCES
            // ========================
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources',
            )

            // ========================
            // PAGES
            // ========================
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages',
            )
            ->pages([
                CustomDashboard::class,
                BudgetGoals::class,
                TransactionsOverview::class,
                CategoriesOverview::class,
            ])

            // ========================
            // WIDGETS
            // ========================
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets',
            )
            ->widgets([
                MonthlyFinanceOverview::class,
                BudgetStatusWidget::class,
            ])

            // ========================
            // MIDDLEWARE
            // ========================
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])

            ->authMiddleware([
                Authenticate::class,
            ])

            // ========================
            // NAVIGATION
            // ========================
            ->navigationItems([
                NavigationItem::make('Leave')
                    ->group('MENU')
                    ->icon('heroicon-o-arrow-left-on-rectangle')
                    ->url('/logout')
                    ->sort(99),
            ]);
    }
}
