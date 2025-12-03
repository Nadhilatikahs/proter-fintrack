<?php

namespace App\Filament\Resources\ReminderSettingResource\Pages;

use App\Filament\Resources\ReminderSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReminderSetting extends EditRecord
{
    protected static string $resource = ReminderSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
