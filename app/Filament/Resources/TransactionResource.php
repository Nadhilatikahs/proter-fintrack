<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Transactions';

    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal transaksi')
                    ->default(now())
                    ->required(),

                Forms\Components\TextInput::make('title')
                    ->label('Nama transaksi')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('type')
                    ->label('Tipe transaksi')
                    ->options([
                        'income'  => 'Income (Pemasukan)',
                        'expense' => 'Expense (Pengeluaran)',
                    ])
                    ->default('expense')
                    ->required(),

                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->options(
                        Category::where('user_id', Auth::id())
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Nama transaksi')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'income' ? 'Income' : 'Expense')
                    ->colors([
                        'success' => 'income',
                        'danger'  => 'expense',
                    ]),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('idr', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->wrap(),
            ])
            ->filters([
                // per hari (hari ini)
                Tables\Filters\Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query) =>
                        $query->whereDate('date', Carbon::today())
                    ),

                // per bulan (bulan ini)
                Tables\Filters\Filter::make('this_month')
                    ->label('Month')
                    ->query(fn (Builder $query) =>
                        $query->whereYear('date', now()->year)
                              ->whereMonth('date', now()->month)
                    ),

                // per tahun (tahun ini)
                Tables\Filters\Filter::make('this_year')
                    ->label('Year')
                    ->query(fn (Builder $query) =>
                        $query->whereYear('date', now()->year)
                    ),
                // "Semua" = tidak pilih filter apa pun (default)
            ])
            ->defaultSort('date', 'desc')
            ->paginated([10, 25, 50, 100, 'all']);
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
