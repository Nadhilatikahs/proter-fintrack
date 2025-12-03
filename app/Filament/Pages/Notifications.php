<?php

namespace App\Filament\Pages;

use App\Models\Reminder;
use Filament\Actions;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Notifications extends Page implements HasTable
{
    use InteractsWithTable;

    /**
     * View Blade yang digunakan.
     */
    protected static string $view = 'filament.pages.notifications';

    /**
     * Label & icon di sidebar.
     */
    protected static ?string $navigationLabel = 'Notifikasi';

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    /**
     * Group menu di sidebar (sesuaikan dengan struktur kamu).
     * Misalnya mau digabung dengan Laporan:
     *
     * protected static ?string $navigationGroup = 'Laporan';
     */
    protected static ?string $navigationGroup = 'Notifikasi';

    /**
     * Urutan di sidebar (angka kecil = lebih atas).
     */
    protected static ?int $navigationSort = 5;

    /**
     * Badge di sidebar (jumlah notifikasi belum dibaca).
     */
    public static function getNavigationBadge(): ?string
    {
        $userId = Auth::id();
        if (! $userId) {
            return null;
        }

        $count = Reminder::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    /**
     * Warna badge di sidebar.
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger'; // merah
    }

    /**
     * Query & konfigurasi tabel notifikasi.
     */
    /** 🔴 HEADER ACTION: tombol "Tandai semua sudah dibaca" */
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_all_as_read')
                ->label('Tandai semua sudah dibaca')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => Reminder::where('user_id', Auth::id())->where('is_read', false)->exists())
                ->action(function () {
                    Reminder::where('user_id', Auth::id())
                        ->where('is_read', false)
                        ->update(['is_read' => true]);
                }),
        ];
    }
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getBaseQuery())
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->since() // contoh: '5 minutes ago'
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Judul')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('message')
                    ->label('Pesan')
                    ->wrap()
                    ->limit(120)
                    ->tooltip(fn (Reminder $record) => $record->message),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(function (string $state) {
                        return match ($state) {
                            'budget_over_limit' => 'Budget terlampaui',
                            'budget_warning'    => 'Budget mendekati batas',
                            'goal_due_soon'     => 'Goal mendekati due',
                            default             => ucfirst(str_replace('_', ' ', $state)),
                        };
                    })
                    ->colors([
                        'danger'  => 'budget_over_limit',
                        'warning' => 'budget_warning',
                        'primary' => 'goal_due_soon',
                    ]),

                IconColumn::make('is_read')
                    ->label('Dibaca?')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_read')
                    ->label('Status')
                    ->trueLabel('Sudah dibaca')
                    ->falseLabel('Belum dibaca')
                    ->placeholder('Semua'),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_as_read')
                    ->label('Tandai sudah dibaca')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (Reminder $record) => ! $record->is_read)
                    ->action(function (Reminder $record) {
                        $record->update(['is_read' => true]);
                    }),

                Tables\Actions\Action::make('mark_as_unread')
                    ->label('Tandai belum dibaca')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->visible(fn (Reminder $record) => $record->is_read)
                    ->action(function (Reminder $record) {
                        $record->update(['is_read' => false]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mark_selected_as_read')
                    ->label('Tandai pilihan sudah dibaca')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (array $records) {
                        Reminder::whereIn('id', $records)->update(['is_read' => true]);
                    }),

                Tables\Actions\BulkAction::make('mark_selected_as_unread')
                    ->label('Tandai pilihan belum dibaca')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->action(function (array $records) {
                        Reminder::whereIn('id', $records)->update(['is_read' => false]);
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 'all']);
    }

    /**
     * Query dasar: notifikasi milik user yang sedang login.
     */
    protected function getBaseQuery(): Builder
    {
        return Reminder::query()
            ->where('user_id', Auth::id());
    }
}
