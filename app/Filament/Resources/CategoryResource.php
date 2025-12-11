<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    // LIST TIDAK MUNCUL DI SIDEBAR, KITA PAKAI PAGE CUSTOM
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Categories';
    protected static ?string $navigationGroup = 'MENU';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Category name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->nullable(),
                    ])
                    ->columns(1) // vertikal, satu kolom
                    ->extraAttributes([
                        'class' => 'ft-form-card', // sudah dipakai di halaman lain
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        // tabel ini tetap dipakai untuk CRUD (create/edit/delete) lewat route filament,
        // tapi BUKAN untuk tampilan utama (kita pakai page custom).
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('transactions_count')
                    ->counts('transactions')
                    ->label('Jumlah transaksi'),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
