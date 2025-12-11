<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\BudgetGoal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    // NAVIGASI KITA MATIKAN, KARENA PAKAI PAGE CUSTOM
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Transactions';
    protected static ?string $navigationGroup = 'MENU';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->label('Date')
                            ->default(now())
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->options(
                                Category::where('user_id', Auth::id())
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'income'  => 'Income',
                                'expense' => 'Expense',
                            ])
                            ->default('expense')
                            ->required()
                            ->live(),

                        // dropdown Goal muncul hanya kalau type = income
                        Forms\Components\Select::make('budget_goal_id')
                            ->label('Goal (optional)')
                            ->options(function () {
                                return BudgetGoal::query()
                                    ->where('user_id', Auth::id())
                                    ->where('type', 'goal')
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->visible(fn (Get $get) => $get('type') === 'income')
                            ->nullable()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('title')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    // Table masih kita biarkan, tapi praktis tidak dipakai lagi di UI
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d F Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Name')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('idr', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->colors([
                        'success' => 'income',
                        'danger'  => 'expense',
                    ]),
            ])
            ->filters([
                Tables\Filters\Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query) =>
                        $query->whereDate('date', Carbon::today())
                    ),

                Tables\Filters\Filter::make('this_month')
                    ->label('This month')
                    ->query(fn (Builder $query) =>
                        $query->whereYear('date', now()->year)
                            ->whereMonth('date', now()->month)
                    ),

                Tables\Filters\Filter::make('this_year')
                    ->label('This year')
                    ->query(fn (Builder $query) =>
                        $query->whereYear('date', now()->year)
                    ),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit'   => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
