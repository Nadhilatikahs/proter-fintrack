<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class TransactionsOverview extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Transactions';
    protected static ?string $navigationGroup = 'MENU';
    protected static ?int    $navigationSort  = 30;

    // slug dan view Blade
    protected static ?string $slug = 'transactions-overview';
    protected static string $view  = 'filament.pages.transactions-overview';

    public ?int $deleteId = null;
    public bool $showDeleteModal = false;

    protected function getViewData(): array
    {
        $userId = Auth::id();
        $filter = request()->query('filter', 'all');
        $today  = Carbon::today();

        $query = Transaction::query()
            ->where('user_id', $userId)
            ->with('category');

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
                $categoryId = request()->query('category_id');
                if ($categoryId) {
                    $query->where('category_id', $categoryId);
                }
                break;

            case 'all':
            default:
                // tidak ada filter
                break;
        }

        $transactions = $query
            ->orderBy('date', 'desc')
            ->get();

        $categories = Category::where('user_id', $userId)
            ->orderBy('name')
            ->get();

        return [
            'transactions'     => $transactions,
            'categories'       => $categories,
            'activeFilter'     => $filter,
            'activeCategoryId' => request()->query('category_id'),
        ];
    }

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

        Transaction::where('user_id', Auth::id())
            ->where('id', $this->deleteId)
            ->delete();

        $this->deleteId        = null;
        $this->showDeleteModal = false;

        $this->dispatch('$refresh');
    }
}
