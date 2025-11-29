<?php

namespace App\Filament\Widgets;

use App\Models\Goal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class GoalOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = Auth::id();

        $totalGoals     = Goal::where('user_id', $userId)->count();
        $completedGoals = Goal::where('user_id', $userId)->whereColumn('current_amount', '>=', 'target_amount')->count();
        $inProgress     = $totalGoals - $completedGoals;

        $avgProgress = Goal::where('user_id', $userId)->get()->avg('progress_percentage') ?? 0;

        return [
            Stat::make('Total goals', (string) $totalGoals)
                ->description('Jumlah tujuan keuangan'),

            Stat::make('Selesai', (string) $completedGoals)
                ->description('Goal yang sudah tercapai')
                ->color('success'),

            Stat::make('Rata-rata progress', $avgProgress . '%')
                ->description($inProgress . ' goal masih berjalan')
                ->color('primary'),
        ];
    }
}
