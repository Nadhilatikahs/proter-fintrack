<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\BudgetGoal;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TransactionsOverview extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Transactions';
    protected static ?string $navigationGroup = 'MENU';
    protected static ?int    $navigationSort  = 30;

    protected static ?string $slug = 'transactions-overview';
    protected static string  $view = 'filament.pages.transactions-overview';

    public ?int  $deleteId        = null;
    public bool  $showDeleteModal = false;

    /*
    |--------------------------------------------------------------------------
    | VIEW DATA
    |--------------------------------------------------------------------------
    */
    protected function getViewData(): array
    {
        $userId = Auth::id();
        $filter = request()->query('filter', 'all');
        $today  = Carbon::today();

        $query = Transaction::query()
            ->where('user_id', $userId)
            ->with(['category', 'budgetGoal']);

        switch ($filter) {
            case 'day':
                $query->whereDate('date', $today);
                break;

            case 'month':
                $query->whereYear('date', $today->year)
                      ->whereMonth('date', $today->month);
                break;

            case 'year':
                $query->whereYear('date', $today->year);
                break;

            case 'category':
                if ($categoryId = request()->query('category_id')) {
                    $query->where('category_id', $categoryId);
                }
                break;

            case 'budget': // drilldown dari Reports
                if ($budgetId = request()->query('budget_goal_id')) {
                    $query->where('type', 'expense')
                          ->where('budget_goal_id', $budgetId);
                }
                break;

            case 'all':
            default:
                // no filter
                break;
        }

        $transactions = $query
            ->orderBy('date', 'desc')
            ->get();

        $categories = Category::where('user_id', $userId)
            ->orderBy('name')
            ->get();

        // 🔑 INI PENTING: dipakai dropdown expense
        $budgets = BudgetGoal::where('user_id', $userId)
            ->where('type', 'budget')
            ->orderBy('name')
            ->get();

        return [
            'transactions'     => $transactions,
            'categories'       => $categories,
            'budgets'          => $budgets,
            'activeFilter'     => $filter,
            'activeCategoryId' => request()->query('category_id'),
            'activeBudgetId'   => request()->query('budget_goal_id'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | STORE TRANSACTION (INI KUNCI UTAMA)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request): void
    {
        $request->validate([
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:1'],
            'date' => ['required', 'date'],
            'category_id' => ['nullable', 'exists:categories,id'],

            // 🔑 WAJIB PAKAI BUDGET SAAT EXPENSE
            'budget_goal_id' => [
                'required_if:type,expense',
                'nullable',
                'exists:budget_goals,id',
            ],
        ]);

        Transaction::create([
            'user_id'        => Auth::id(),
            'type'           => $request->type,
            'amount'         => $request->amount,
            'date'           => $request->date,
            'category_id'    => $request->category_id,
            'budget_goal_id' => $request->type === 'expense'
                ? $request->budget_goal_id
                : null,
            'title'          => $request->title ?? null,
            'description'    => $request->description ?? null,
        ]);

        // refresh page
        $this->dispatch('$refresh');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteConfirmed(): void
    {
        Transaction::where('id', $this->deleteId)
            ->where('user_id', Auth::id())
            ->delete();

        $this->deleteId = null;
        $this->showDeleteModal = false;

        $this->dispatch('$refresh');
    }

}
