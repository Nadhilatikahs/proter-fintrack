<?php

namespace App\Filament\Pages;

use App\Models\Goal;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FinancialGoalReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationLabel = 'Financial Goal Report';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 30;

    protected static string $view = 'filament.pages.financial-goal-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Goal::query()
                    ->where('user_id', Auth::id())
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tujuan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('target_amount')
                    ->money('idr', true)
                    ->label('Target'),

                Tables\Columns\TextColumn::make('current_amount')
                    ->money('idr', true)
                    ->label('Terkumpul'),

                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->suffix('%'),

                Tables\Columns\TextColumn::make('target_date')
                    ->date('d M Y')
                    ->label('Target date'),

                Tables\Columns\IconColumn::make('is_completed')
                    ->boolean()
                    ->label('Selesai?'),
            ])
            ->defaultSort('target_date', 'asc')
            ->paginated([10, 25, 50, 'all']);
    }
}
