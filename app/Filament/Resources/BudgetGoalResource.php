<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetGoalResource\Pages;
use App\Models\BudgetGoal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Rules\MaxWords;


class BudgetGoalResource extends Resource
{
    protected static ?string $model = BudgetGoal::class;
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'MENU';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int    $navigationSort = 10;
    protected static ?string $navigationLabel = 'Budget & Goals';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // =======================
                // SECTION INFO UTAMA
                // =======================
                Forms\Components\Section::make('Let`s achieve it!')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->nullable()
                            ->rule(new MaxWords(50)),

                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'budget' => 'Budget',
                                'goal'   => 'Goal',
                            ])
                            ->required()
                            ->live(),
                    ])
                    // <<< di sini kuncinya: 1 kolom, jadi semua field turun ke bawah
                    ->columns(1),

                // =======================
                // SECTION DETAIL
                // =======================
                Forms\Components\Section::make('Detail')
                    ->schema([
                        Forms\Components\TextInput::make('target_amount')
                            ->label('Target / Max Amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        Forms\Components\Select::make('period_type')
                            ->label('Budget Period')
                            ->options([
                                'daily'    => 'Dayly',
                                'weekly'   => 'Weekly',
                                'biweekly' => 'Biweekly',
                                'monthly'  => 'Monthly',
                                'yearly'   => 'Yearly',
                            ])
                            ->visible(fn (Get $get) => $get('type') === 'budget')
                            ->required(fn (Get $get) => $get('type') === 'budget'),

                        Forms\Components\DatePicker::make('target_date')
                            ->label('Target Date')
                            ->visible(fn (Get $get) => $get('type') === 'goal')
                            ->required(fn (Get $get) => $get('type') === 'goal')
                            ->nullable(),
                    ])
                    // juga 1 kolom supaya Target / Periode / Tanggal turun ke bawah
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'budget' ? 'Budget' : 'Goal')
                    ->colors([
                        'info'    => 'budget',
                        'success' => 'goal',
                    ]),

                Tables\Columns\TextColumn::make('period_type')
                    ->label('Periode')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->type !== 'budget') {
                            return '-';
                        }
                        return match ($state) {
                            'daily'    => 'Per hari',
                            'weekly'   => 'Per minggu',
                            'biweekly' => 'Per 2 minggu',
                            'monthly'  => 'Per bulan',
                            'yearly'   => 'Per tahun',
                            default    => '-',
                        };
                    }),

                Tables\Columns\TextColumn::make('target_amount')
                    ->label('Nominal')
                    ->money('idr', true),

                Tables\Columns\TextColumn::make('target_date')
                    ->label('Target date')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Filter tipe')
                    ->options([
                        'budget' => 'Budget',
                        'goal'   => 'Goal',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBudgetGoals::route('/'),
            'create' => Pages\CreateBudgetGoal::route('/create'),
            'edit'   => Pages\EditBudgetGoal::route('/{record}/edit'),
        ];
    }
}
