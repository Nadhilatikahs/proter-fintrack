<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GoalResource\Pages;
use App\Models\Category;
use App\Models\Goal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class GoalResource extends Resource
{
    protected static ?string $model = Goal::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationLabel = 'Financial Goals';

    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama tujuan')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('target_amount')
                    ->label('Target dana')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\TextInput::make('current_amount')
                    ->label('Dana terkumpul saat ini')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->required(),

                Forms\Components\DatePicker::make('target_date')
                    ->label('Target tercapai')
                    ->nullable(),

                Forms\Components\Select::make('category_id')
                    ->label('Kategori tabungan (optional)')
                    ->options(
                        Category::query()
                            ->where('user_id', Auth::id())
                            ->where('type', 'income') // misal tabungan dianggap income / category khusus
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3)
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tujuan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('target_amount')
                    ->label('Target')
                    ->money('idr', true),

                Tables\Columns\TextColumn::make('current_amount')
                    ->label('Terkumpul')
                    ->money('idr', true),

                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->suffix('%')
                    ->color(function ($record) {
                        return $record->is_completed ? 'success' : 'primary';
                    }),

                Tables\Columns\TextColumn::make('target_date')
                    ->label('Target date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Selesai?')
                    ->boolean(),
            ])
            ->defaultSort('target_date', 'asc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGoals::route('/'),
            'create' => Pages\CreateGoal::route('/create'),
            'edit'   => Pages\EditGoal::route('/{record}/edit'),
        ];
    }
}
