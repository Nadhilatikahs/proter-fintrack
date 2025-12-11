<?php

namespace App\Filament\Resources\BudgetGoalResource\Pages;

use App\Filament\Resources\BudgetGoalResource;
use Filament\Resources\Pages\EditRecord;

class EditBudgetGoal extends EditRecord
{
    protected static string $resource = BudgetGoalResource::class;

    /**
     * Setelah edit, kembali ke list.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Tombol: Save + Cancel saja.
     */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Save'),
            $this->getCancelFormAction()
                ->label('Cancel'),
        ];
    }
}
