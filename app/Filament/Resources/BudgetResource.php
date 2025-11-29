<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetResource\Pages;
use App\Models\Budget;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationLabel = 'Budgets';

    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Kategori (optional)')
                    ->options(
                        Category::query()
                            ->where('user_id', Auth::id())
                            ->where('type', 'expense')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\Select::make('month')
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
                    ])
                    ->default(now()->month)
                    ->required(),

                Forms\Components\TextInput::make('year')
                    ->numeric()
                    ->default(now()->year)
                    ->minValue(2000)
                    ->maxValue(2100)
                    ->required(),

                Forms\Components\TextInput::make('limit_amount')
                    ->label('Limit pengeluaran')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->money('idr', true),

                Tables\Columns\TextColumn::make('spent')
                    ->label('Terpakai')
                    ->money('idr', true),

                Tables\Columns\TextColumn::make('remaining')
                    ->label('Sisa')
                    ->money('idr', true)
                    ->color(fn ($record) => $record->remaining < 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('usage_percentage')
                    ->label('% Terpakai')
                    ->suffix('%')
                    ->color(function ($record) {
                        return match (true) {
                            $record->usage_percentage >= 100 => 'danger',
                            $record->usage_percentage >= 80  => 'warning',
                            default                           => 'success',
                        };
                    }),
            ])
            ->defaultSort('year', 'desc')
            ->defaultSort('month', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit'   => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}
