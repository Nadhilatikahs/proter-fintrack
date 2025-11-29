<?php

namespace App\Filament\Pages;

use App\Models\Budget;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BudgetReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Budget Report';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.budget-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Budget::query()
                    ->with('category')
                    ->where('user_id', Auth::id())
            )
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->placeholder('Semua kategori'),

                Tables\Columns\TextColumn::make('month')
                    ->label('Bulan')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::create()->month($state)->translatedFormat('F')),

                Tables\Columns\TextColumn::make('year'),

                Tables\Columns\TextColumn::make('limit_amount')
                    ->label('Budget')
                    ->money('idr', true)
                    ->summarize(Sum::make()->label('Total budget')->money('idr', true)),

                Tables\Columns\TextColumn::make('spent')
                    ->label('Terpakai')
                    ->money('idr', true)
                    ->summarize(Sum::make()->label('Total terpakai')->money('idr', true)),

                Tables\Columns\TextColumn::make('remaining')
                    ->label('Sisa')
                    ->money('idr', true),

                Tables\Columns\TextColumn::make('usage_percentage')
                    ->label('% terpakai')
                    ->suffix('%'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('month')
                    ->label('Bulan')
                    ->options([
                        1 => 'Januari',
                        2 => 'Februari',
                        3 => 'Maret',
                        4 => 'April',
                        5 => 'Mei',
                        6 => 'Juni',
                        7 => 'Juli',
                        8 => 'Agustus',
                        9 => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember',
                    ]),
                Tables\Filters\SelectFilter::make('year')
                    ->options(
                        Budget::where('user_id', Auth::id())
                            ->select('year')
                            ->distinct()
                            ->orderByDesc('year')
                            ->pluck('year', 'year')
                    ),
            ])
            ->defaultSort('year', 'desc')
            ->defaultSort('month', 'desc')
            ->paginated([10, 25, 50, 'all']);
    }
}
