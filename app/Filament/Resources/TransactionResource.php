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
use App\Rules\MaxWords;


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
        $userId = Auth::id();

        return $form
            ->schema([
                Forms\Components\Section::make('') // kartu hijau besar
                    ->schema([
                        // ROW 1 : Date & Category
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('date')
                                    ->label('Date')
                                    ->default(now())
                                    ->required()
                                    ->displayFormat('M d, Y')
                                    ->native(false) // paksa pakai flatpickr
                                    ->extraAttributes([
                                        'class' => 'ft-input',
                                    ]),

                                Forms\Components\Select::make('category_id')
                                    ->label('Category')
                                    ->options(
                                        Category::where('user_id', $userId)
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->extraAttributes([
                                        'class' => 'ft-input ft-select',
                                    ]),
                            ]),

                        // ROW 2 : Type & Name
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Type')
                                    ->options([
                                        'income'  => 'Income',
                                        'expense' => 'Expense',
                                    ])
                                    ->required()
                                    ->default('expense')
                                    ->live()
                                    ->extraAttributes([
                                        'class' => 'ft-input ft-select',
                                    ]),

                                Forms\Components\TextInput::make('title')
                                    ->label('Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->extraAttributes([
                                        'class' => 'ft-input',
                                    ]),
                            ]),

                        // ROW 3 : Amount & Goals (optional, hanya untuk income)
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->label('Amount')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->extraAttributes([
                                        'class' => 'ft-input',
                                    ]),

                                Forms\Components\Select::make('budget_goal_id')
                                    ->label('Related Goal (optional)')
                                    ->options(
                                        BudgetGoal::where('user_id', $userId)
                                            ->where('type', 'goal')
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                    )
                                    ->visible(fn (Get $get) => $get('type') === 'income')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->extraAttributes([
                                        'class' => 'ft-input ft-select',
                                    ]),
                            ]),

                        // ROW 4 : Description (full width + jarak)
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->nullable()
                            ->rule(new MaxWords(50))
                            ->columnSpanFull()
                            ->extraAttributes([
                                'class' => 'ft-input mt-3',
                            ]),
                    ])
                    ->extraAttributes([
                        'class' => 'ft-card-form',
                    ]),
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
