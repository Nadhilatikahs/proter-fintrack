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

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Transactions';
    protected static ?string $navigationGroup = 'MENU';
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-path';
    protected static ?int    $navigationSort  = 30;

    /* =====================================================
     | FORM
     ===================================================== */
    public static function form(Form $form): Form
    {
        $userId = Auth::id();

        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([

                        /* ROW 1 : DATE & CATEGORY */
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('date')
                                    ->label('Date')
                                    ->default(now())
                                    ->required()
                                    ->native(false)
                                    ->extraAttributes(['class' => 'ft-input']),

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
                                    ->extraAttributes(['class' => 'ft-input ft-select']),
                            ]),

                        /* ROW 2 : TYPE & TITLE */
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Type')
                                    ->options([
                                        'income'  => 'Income',
                                        'expense' => 'Expense',
                                    ])
                                    ->required()
                                    ->live()
                                    ->extraAttributes(['class' => 'ft-input ft-select']),

                                Forms\Components\TextInput::make('title')
                                    ->label('Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->extraAttributes(['class' => 'ft-input']),
                            ]),

                        /* ROW 3 : AMOUNT & BUDGET / GOAL */
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->label('Amount')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->extraAttributes(['class' => 'ft-input']),

                                /* 🔴 BUDGET (WAJIB JIKA EXPENSE) */
                                Forms\Components\Select::make('budget_goal_id')
                                    ->label(fn (Get $get) =>
                                        $get('type') === 'expense'
                                            ? 'Budget *'
                                            : 'Goal (optional)'
                                    )
                                    ->options(fn (Get $get) =>
                                        BudgetGoal::where('user_id', $userId)
                                            ->where('type', $get('type') === 'expense' ? 'budget' : 'goal')
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                    )
                                    ->required(fn (Get $get) => $get('type') === 'expense')
                                    ->searchable()
                                    ->preload()
                                    ->helperText(fn (Get $get) =>
                                        $get('type') === 'expense'
                                            ? 'Wajib dipilih untuk transaksi pengeluaran'
                                            : 'Opsional untuk pemasukan'
                                    )
                                    ->extraAttributes(['class' => 'ft-input ft-select']),
                            ]),

                        /* ROW 4 : DESCRIPTION */
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->nullable()
                            ->rule(new MaxWords(50))
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'ft-input mt-3']),
                    ])
                    ->extraAttributes(['class' => 'ft-card-form']),
            ]);
    }

    /* =====================================================
     | TABLE (OPSIONAL)
     ===================================================== */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->date('d F Y')->sortable(),
                Tables\Columns\TextColumn::make('title')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('category.name')->label('Category'),
                Tables\Columns\TextColumn::make('amount')->money('idr', true),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'success' => 'income',
                        'danger'  => 'expense',
                    ]),
                Tables\Columns\TextColumn::make('budgetGoal.name')
                    ->label('Budget / Goal')
                    ->placeholder('-'),
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
