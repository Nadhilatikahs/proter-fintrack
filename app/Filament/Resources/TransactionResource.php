<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('date')->required(),
            Forms\Components\TextInput::make('description')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('category_id')
                ->relationship('category', 'name')
                ->required(),
            Forms\Components\TextInput::make('amount')
                ->numeric()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('date')->date(),
            Tables\Columns\TextColumn::make('description')->searchable(),
            Tables\Columns\TextColumn::make('category.name')->label('Category'),
            Tables\Columns\TextColumn::make('amount')->money('idr', true),
        ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
