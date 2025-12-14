<?php

namespace App\Filament\Pages;

use App\Models\Reminder;
use Filament\Actions;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Notifications extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.notifications';

    // ⬇️ penting: jangan tampil di sidebar
    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'Notifikasi';
    }

    /** Tombol header: tandai semua sudah dibaca & clear all */
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_all_as_read')
                ->label('Tandai semua sudah dibaca')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn() => Reminder::where('user_id', Auth::id())->where('is_read', false)->exists())
                ->action(function () {
                    Reminder::where('user_id', Auth::id())
                        ->where('is_read', false)
                        ->update(['is_read' => true]);

                    Notification::make()
                        ->title('Semua notifikasi ditandai sudah dibaca')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('clear_all')
                ->label('Hapus Semua')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Hapus Semua Notifikasi')
                ->modalDescription('Apakah kamu yakin ingin menghapus semua notifikasi? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus Semua')
                ->visible(fn() => Reminder::where('user_id', Auth::id())->exists())
                ->action(function () {
                    $count = Reminder::where('user_id', Auth::id())->count();
                    Reminder::where('user_id', Auth::id())->delete();

                    Notification::make()
                        ->title("{$count} notifikasi berhasil dihapus")
                        ->success()
                        ->send();
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
                    ->since()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Judul')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('message')
                    ->label('Pesan')
                    ->wrap()
                    ->limit(120)
                    ->tooltip(fn(Reminder $record) => $record->message),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(function (string $state) {
                        return match ($state) {
                            'budget_over_limit' => 'Budget terlampaui',
                            'budget_warning' => 'Budget mendekati batas',
                            'goal_due_soon' => 'Goal mendekati due',
                            default => ucfirst(str_replace('_', ' ', $state)),
                        };
                    })
                    ->colors([
                        'danger' => 'budget_over_limit',
                        'warning' => 'budget_warning',
                        'primary' => 'goal_due_soon',
                    ]),

                IconColumn::make('is_read')
                    ->label('Dibaca?')
                    ->boolean(),
            ])
            ->actions([
                // Mark as read action
                Action::make('mark_read')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->tooltip('Tandai sudah dibaca')
                    ->visible(fn(Reminder $record) => !$record->is_read)
                    ->action(function (Reminder $record) {
                        $record->update(['is_read' => true]);

                        Notification::make()
                            ->title('Notifikasi ditandai sudah dibaca')
                            ->success()
                            ->send();
                    }),

                // Delete action (X button)
                DeleteAction::make()
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->tooltip('Hapus notifikasi')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Notifikasi')
                    ->modalDescription('Apakah kamu yakin ingin menghapus notifikasi ini?')
                    ->successNotificationTitle('Notifikasi berhasil dihapus'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 'all']);
    }

    protected function getBaseQuery(): Builder
    {
        return Reminder::query()
            ->where('user_id', Auth::id());
    }
}
