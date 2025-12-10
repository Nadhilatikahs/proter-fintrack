<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReminderSettingResource\Pages;
use App\Models\ReminderSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReminderSettingResource extends Resource
{
    protected static ?string $model = ReminderSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationLabel = 'Reminder Settings';

    //protected static ?string $navigationGroup = 'Notifikasi';

    protected static ?int $navigationSort = 1;
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pengaturan Budget')
                    ->schema([
                        Forms\Components\TextInput::make('budget_warning_threshold')
                            ->label('Warning budget (%)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->suffix('%')
                            ->default(80)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pengaturan Financial Goals')
                    ->schema([
                        Forms\Components\TextInput::make('goal_days_before_due')
                            ->label('Reminder sebelum due (hari)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(365)
                            ->suffix('hari')
                            ->default(7)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Jadwal & Channel')
                    ->schema([
                        Forms\Components\TextInput::make('daily_digest_hour')
                            ->label('Jam pengecekan harian')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(23)
                            ->suffix('WIB')
                            ->default(20)
                            ->required(),

                        Forms\Components\Toggle::make('notify_email')
                            ->label('Kirim lewat email')
                            ->inline(false)
                            ->default(true),

                        Forms\Components\Toggle::make('notify_in_app')
                            ->label('Tampilkan di notifikasi aplikasi')
                            ->inline(false)
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('budget_warning_threshold')
                    ->label('Budget warning')
                    ->suffix('%'),

                Tables\Columns\TextColumn::make('goal_days_before_due')
                    ->label('Goal H-')
                    ->suffix(' hari'),

                Tables\Columns\TextColumn::make('daily_digest_hour')
                    ->label('Jam cek')
                    ->formatStateUsing(fn ($state) => $state . ':00'),

                Tables\Columns\IconColumn::make('notify_email')
                    ->label('Email')
                    ->boolean(),

                Tables\Columns\IconColumn::make('notify_in_app')
                    ->label('In-app')
                    ->boolean(),
            ])
            ->defaultSort('id', 'asc');
    }

    /**
     * Scope: hanya setting milik user yang login.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListReminderSettings::route('/'),
            'create' => Pages\CreateReminderSetting::route('/create'),
            'edit'   => Pages\EditReminderSetting::route('/{record}/edit'),
        ];
    }
}
