<?php

namespace App\Filament\Resources\BudgetGoalResource\Pages;

use App\Filament\Resources\BudgetGoalResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBudgetGoal extends CreateRecord
{
    protected static string $resource = BudgetGoalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}
