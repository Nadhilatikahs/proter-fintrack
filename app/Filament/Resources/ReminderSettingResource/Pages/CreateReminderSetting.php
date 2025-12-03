<?php

namespace App\Filament\Resources\ReminderSettingResource\Pages;

use App\Filament\Resources\ReminderSettingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateReminderSetting extends CreateRecord
{
    protected static string $resource = ReminderSettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}
