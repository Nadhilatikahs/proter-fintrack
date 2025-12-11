<?php

namespace App\Filament\Pages;

use App\Models\BudgetGoal;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class BudgetGoals extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Budget & Goals';
    protected static ?string $navigationGroup = 'MENU';
    protected static ?int    $navigationSort  = 10;

    // PENTING: slug beda dengan resource, supaya tidak tabrakan route
    protected static ?string $slug = 'budget-goals-overview';

    protected static string $view = 'filament.pages.budget-goals';

    // Livewire properties untuk modal delete
    public ?int $deleteId = null;
    public bool $showDeleteModal = false;

    protected function getViewData(): array
    {
        $userId = Auth::id();

        $budgets = BudgetGoal::where('user_id', $userId)
            ->where('type', 'budget')
            ->get();

        $goals = BudgetGoal::where('user_id', $userId)
            ->where('type', 'goal')
            ->get();

        foreach ($budgets as $budget) {
            $spent = $this->calculateBudgetSpent($budget);
            $budget->spent = $spent;
            $budget->progress = $budget->target_amount > 0
                ? round(min(100, ($spent / $budget->target_amount) * 100))
                : 0;

            $budget->period_label = $this->formatPeriod($budget->period_type);
        }

        foreach ($goals as $goal) {
            $saved = $this->calculateGoalSaved($goal);
            $goal->saved = $saved;
            $goal->progress = $goal->target_amount > 0
                ? round(min(100, ($saved / $goal->target_amount) * 100))
                : 0;

            $goal->deadline_label = $goal->target_date
                ? Carbon::parse($goal->target_date)->format('d M Y')
                : '-';
        }

        $totalGoals       = $goals->count();
        $totalAchieved    = $goals->where('progress', '>=', 100)->count();
        $totalBudgetLimit = $budgets->sum('target_amount');
        $totalBudgetSpent = $budgets->sum('spent');
        $remainingBudget  = max(0, $totalBudgetLimit - $totalBudgetSpent);

        return [
            'budgets'          => $budgets,
            'goals'            => $goals,
            'totalGoals'       => $totalGoals,
            'totalAchieved'    => $totalAchieved,
            'totalBudgetLimit' => $totalBudgetLimit,
            'totalBudgetSpent' => $totalBudgetSpent,
            'remainingBudget'  => $remainingBudget,
        ];
    }

    // ===== Actions dari modal =====

    public function confirmDelete(int $id): void
    {
        $this->deleteId       = $id;
        $this->showDeleteModal = true;
    }

    public function deleteConfirmed(): void
    {
        if (! $this->deleteId) {
            $this->showDeleteModal = false;
            return;
        }

        BudgetGoal::where('user_id', Auth::id())
            ->where('id', $this->deleteId)
            ->delete();

        $this->deleteId        = null;
        $this->showDeleteModal = false;

        $this->dispatch('$refresh');
    }

    // ===== Perhitungan =====

    protected function calculateBudgetSpent(BudgetGoal $budget): float
    {
        $userId = $budget->user_id;
        $now    = Carbon::now();

        [$start, $end] = $this->getPeriodRange($budget->period_type ?? 'monthly', $now);

        return Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');
    }

    protected function calculateGoalSaved(BudgetGoal $goal): float
    {
        if (! $goal->target_amount || $goal->target_amount <= 0) {
            return 0;
        }

        return Transaction::query()
            ->where('user_id', $goal->user_id)
            ->where('type', 'income')
            ->when($goal->category_id ?? null, fn ($q, $categoryId) =>
                $q->where('category_id', $categoryId)
            )
            ->sum('amount');
    }

    protected function getPeriodRange(string $periodType, Carbon $now): array
    {
        $start = $now->copy();
        $end   = $now->copy();

        return match ($periodType) {
            'daily'    => [$start->startOfDay(),   $end->endOfDay()],
            'weekly'   => [$start->startOfWeek(),  $end->endOfWeek()],
            'biweekly' => [$start->copy()->subDays(13)->startOfDay(), $end->endOfDay()],
            'yearly'   => [$start->startOfYear(),  $end->endOfYear()],
            default    => [$start->startOfMonth(), $end->endOfMonth()],
        };
    }

    protected function formatPeriod(?string $periodType): string
    {
        return match ($periodType) {
            'daily'    => 'Per day',
            'weekly'   => 'Weekly',
            'biweekly' => 'Bi-weekly',
            'monthly'  => 'Monthly',
            'yearly'   => 'Yearly',
            default    => '-',
        };
    }
}
