<?php

namespace App\Filament\Resources\BudgetGoalResource\Pages;

use App\Filament\Resources\BudgetGoalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBudgetGoals extends ListRecords
{
    protected static string $resource = BudgetGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
