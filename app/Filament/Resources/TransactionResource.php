<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationLabel = 'Transactions';
    protected static ?string $navigationGroup = 'MENU';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?int $navigationSort = 30;

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
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d F Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Name')
                    ->searchable()
                    ->wrap(),

                // Category pill biru
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->html()
                    ->getStateUsing(function (Transaction $record) {
                        $name = $record->category->name ?? '—';
                        return "<span class=\"ft-pill ft-pill-category\">{$name}</span>";
                    }),

                // Amount
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('idr', true)
                    ->sortable(),

                // Income / Expense pill
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->html()
                    ->getStateUsing(function (Transaction $record) {
                        $label = $record->type === 'income' ? 'Income' : 'Expense';
                        $class = $record->type === 'income'
                            ? 'ft-pill ft-pill-income'
                            : 'ft-pill ft-pill-expense';

                        return "<span class=\"{$class}\">{$label}</span>";
                    }),
            ])
            ->filters([
                // ALL → tidak mengubah query, hanya supaya chip "All" ada
                Tables\Filters\Filter::make('all')
                    ->label('All')
                    ->query(fn (Builder $query) => $query),

                // DAY → hari ini
                Tables\Filters\Filter::make('day')
                    ->label('Day')
                    ->query(fn (Builder $query) =>
                        $query->whereDate('date', Carbon::today())
                    ),

                // MONTH → bulan ini
                Tables\Filters\Filter::make('month')
                    ->label('Month')
                    ->query(fn (Builder $query) =>
                        $query->whereYear('date', now()->year)
                              ->whereMonth('date', now()->month)
                    ),

                // CATEGORY → dropdown kategori
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->button()
                    ->extraAttributes(['class' => 'fin-btn-dark']),

                Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->button()
                    ->extraAttributes(['class' => 'fin-btn-red'])
                    ->requiresConfirmation()
                    ->modalHeading('Are you sure you want to delete this transaction?')
                    ->modalDescription('This action cannot be undone.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete selected transactions?')
                        ->modalDescription('This action cannot be undone.'),
                ]),
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
