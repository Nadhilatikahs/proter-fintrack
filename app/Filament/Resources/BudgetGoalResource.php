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

class BudgetGoalResource extends Resource
{
    protected static ?string $model = BudgetGoal::class;

    /**
     * Resource ini TIDAK muncul sendiri di sidebar.
     * Sidebar pakai Page custom BudgetGoals.
     */
    protected static bool $shouldRegisterNavigation = false;

    // Tetap boleh diset untuk dipakai di title dsb
    protected static ?string $navigationLabel = 'Budget & Goals';
    protected static ?string $navigationGroup = 'MENU';
    protected static ?string $navigationIcon  = 'heroicon-o-banknotes';
    protected static ?int    $navigationSort  = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Info utama')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->nullable(),

                        Forms\Components\Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'budget' => 'Budget',
                                'goal'   => 'Goal',
                            ])
                            ->required()
                            ->live(), // reaktif untuk field lain
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail')
                    ->schema([
                        Forms\Components\Select::make('period_type')
                            ->label('Periode budget')
                            ->options([
                                'daily'    => 'Per hari',
                                'weekly'   => 'Per minggu',
                                'biweekly' => 'Per 2 minggu',
                                'monthly'  => 'Per bulan',
                                'yearly'   => 'Per tahun',
                            ])
                            ->visible(fn (Get $get) => $get('type') === 'budget')
                            ->required(fn (Get $get) => $get('type') === 'budget'),

                        Forms\Components\TextInput::make('target_amount')
                            ->label('Target / Batas nominal')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        Forms\Components\DatePicker::make('target_date')
                            ->label('Tanggal pencapaian')
                            ->visible(fn (Get $get) => $get('type') === 'goal')
                            ->required(fn (Get $get) => $get('type') === 'goal')
                            ->nullable(),
                    ])
                    ->columns(3),
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

                // Badge Budget / Goal ala pill
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->html()
                    ->getStateUsing(function (BudgetGoal $record) {
                        $label = $record->type === 'budget' ? 'Budget' : 'Goal';

                        $class = $record->type === 'budget'
                            ? 'ft-pill ft-pill-income'
                            : 'ft-pill ft-pill-category';

                        return "<span class=\"{$class}\">{$label}</span>";
                    }),

                Tables\Columns\TextColumn::make('period_type')
                    ->label('Periode')
                    ->formatStateUsing(function ($state, BudgetGoal $record) {
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
                    ->modalHeading('Delete this budget/goal?')
                    ->modalDescription('This action cannot be undone.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete selected budget/goals?')
                        ->modalDescription('This action cannot be undone.'),
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
