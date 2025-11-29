<?php

namespace App\Filament\Resources\GoalResource\Pages;

use App\Filament\Resources\GoalResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateGoal extends CreateRecord
{
    protected static string $resource = GoalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}
