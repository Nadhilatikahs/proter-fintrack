<?php

namespace App\Filament\Resources\BudgetGoalResource\Pages;

use App\Filament\Resources\BudgetGoalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBudgetGoal extends EditRecord
{
    protected static string $resource = BudgetGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
