<?php

namespace App\Filament\Resources\ReminderSettingResource\Pages;

use App\Filament\Resources\ReminderSettingResource;
use App\Models\ReminderSetting;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListReminderSettings extends ListRecords
{
    protected static string $resource = ReminderSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn (): bool => ! ReminderSetting::where('user_id', Auth::id())->exists()),
        ];
    }
}
