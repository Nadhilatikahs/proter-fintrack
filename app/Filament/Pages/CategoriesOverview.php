<?php

namespace App\Filament\Pages;

use App\Models\Category;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class CategoriesOverview extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'Categories';
    protected static ?string $navigationGroup = 'MENU';
    protected static ?int    $navigationSort  = 20;

    // judul page di tab + breadcrumb
    protected static ?string $title = 'Categories';

    // kita pakai view custom
    protected static string $view = 'filament.pages.categories-overview';

    public ?int $deleteId = null;
    public bool $showDeleteModal = false;

    /**
     * Jangan tampilkan heading default Filament
     * (yang tadi muncul sebagai "Categories Overview").
     */
    protected function hasPageHeading(): bool
    {
        return false;
    }

    protected function getViewData(): array
    {
        $userId = Auth::id();

        $categories = Category::query()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->get();

        return [
            'categories' => $categories,
        ];
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteConfirmed(): void
    {
        if (! $this->deleteId) {
            $this->showDeleteModal = false;
            return;
        }

        Category::where('user_id', Auth::id())
            ->where('id', $this->deleteId)
            ->delete();

        $this->deleteId = null;
        $this->showDeleteModal = false;

        $this->dispatch('$refresh');
    }
}
