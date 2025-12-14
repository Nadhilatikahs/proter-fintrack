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

    protected static ?string $slug = 'budget-goals-overview';
    protected static string  $view = 'filament.pages.budget-goals';

    public ?int  $deleteId        = null;
    public bool  $showDeleteModal = false;

    protected function getViewData(): array
    {
        $userId = Auth::id();
        $now    = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | BUDGETS
        |--------------------------------------------------------------------------
        */
        $budgets = BudgetGoal::where('user_id', $userId)
            ->budgets()
            ->get()
            ->map(function (BudgetGoal $budget) use ($now) {

                [$start, $end] = $this->getPeriodRange(
                    $budget->period_type ?? 'monthly',
                    $now
                );

                // 🔑 INTI PERBAIKAN: HUBUNG KE budget_goal_id
                $spent = Transaction::where('user_id', $budget->user_id)
                    ->where('type', 'expense')
                    ->where('budget_goal_id', $budget->id)
                    ->whereBetween('date', [
                        $start->toDateString(),
                        $end->toDateString(),
                    ])
                    ->sum('amount');

                $target = (int) ($budget->target_amount ?? 0);
                $spent  = (int) $spent;

                $progress = $target > 0
                    ? round(min(100, ($spent / $target) * 100))
                    : 0;

                // STATUS
                if ($progress >= 80) {
                    $status = 'danger';
                } elseif ($progress >= 50) {
                    $status = 'warning';
                } else {
                    $status = 'safe';
                }

                $budget->spent        = $spent;
                $budget->progress     = $progress;
                $budget->status       = $status;
                $budget->period_label = $this->formatPeriod($budget->period_type);
                $budget->remaining    = max($target - $spent, 0);

                return $budget;
            });

        /*
        |--------------------------------------------------------------------------
        | GOALS (SUDAH BENAR, DIKUNCI)
        |--------------------------------------------------------------------------
        */
        $goals = BudgetGoal::where('user_id', $userId)
            ->goals()
            ->get()
            ->map(function (BudgetGoal $goal) {

                $saved = Transaction::where('user_id', $goal->user_id)
                    ->where('type', 'income')
                    ->where('budget_goal_id', $goal->id)
                    ->sum('amount');

                $target = (int) ($goal->target_amount ?? 0);
                $saved  = (int) $saved;

                $progress = $target > 0
                    ? round(min(100, ($saved / $target) * 100))
                    : 0;

                $goal->saved          = $saved;
                $goal->progress       = $progress;
                $goal->deadline_label = $goal->target_date
                    ? Carbon::parse($goal->target_date)->format('d M Y')
                    : '-';

                return $goal;
            });

        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */
        $totalGoals       = $goals->count();
        $totalAchieved    = $goals->where('progress', '>=', 100)->count();
        $totalBudgetLimit = $budgets->sum('target_amount');
        $totalBudgetSpent = $budgets->sum('spent');
        $remainingBudget  = max(0, $totalBudgetLimit - $totalBudgetSpent);

        return compact(
            'budgets',
            'goals',
            'totalGoals',
            'totalAchieved',
            'totalBudgetLimit',
            'totalBudgetSpent',
            'remainingBudget'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function confirmDelete(int $id): void
    {
        $this->deleteId        = $id;
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

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    protected function getPeriodRange(string $periodType, Carbon $now): array
    {
        return match ($periodType) {
            'daily'    => [$now->copy()->startOfDay(),   $now->copy()->endOfDay()],
            'weekly'   => [$now->copy()->startOfWeek(),  $now->copy()->endOfWeek()],
            'biweekly' => [$now->copy()->subDays(13)->startOfDay(), $now->copy()->endOfDay()],
            'yearly'   => [$now->copy()->startOfYear(),  $now->copy()->endOfYear()],
            default    => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
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
