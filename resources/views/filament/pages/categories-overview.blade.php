<x-filament-panels::page>
    <div class="ft-cat-layout">
        {{-- HEADER TITLE + BUTTON --}}
        <div class="ft-cat-header">
            <h1 class="ft-page-title">Categories</h1>

            <a
                href="{{ route('filament.admin.resources.categories.create') }}"
                class="fin-btn-outline-green"
            >
                + Input Categories
            </a>
        </div>

        {{-- HEAD ROW (Date / Category / Actions) --}}
        <div class="ft-cat-table-head">
            <span class="ft-cat-head-label">Date</span>
            <span class="ft-cat-head-label">Category</span>
            <span class="ft-cat-head-label text-right">Actions</span>
        </div>

        {{-- LIST ROW --}}
        <div class="ft-cat-list">
            @forelse($categories as $category)
                <article class="ft-cat-row">
                    <div class="ft-cat-date">
                        {{ optional($category->created_at)->format('d F Y') ?? '-' }}
                    </div>

                    <div class="ft-cat-name">
                        <span class="ft-pill ft-pill-category">
                            {{ $category->name }}
                        </span>
                    </div>

                    <div class="ft-cat-actions">
                        <a
                            href="{{ route('filament.admin.resources.categories.edit', $category) }}"
                            class="fin-btn-dark"
                        >
                            Edit
                        </a>

                        <button
                            type="button"
                            class="fin-btn-red"
                            wire:click="confirmDelete({{ $category->id }})"
                        >
                            Delete
                        </button>
                    </div>
                </article>
            @empty
                <p class="ft-empty-text">
                    Belum ada kategori.
                    Klik <strong>+ Input Categories</strong> untuk menambahkan.
                </p>
            @endforelse
        </div>

        {{-- MODAL DELETE --}}
        @if($showDeleteModal)
            <div class="ft-modal-backdrop">
                <div class="ft-modal">
                    <div class="ft-modal-title">
                        Delete category
                    </div>
                    <div class="ft-modal-text">
                        Are you sure you want to delete this category?<br>
                        <span class="text-red-600 font-semibold">
                            This action cannot be undone.
                        </span>
                    </div>
                    <div class="ft-modal-actions">
                        <button
                            type="button"
                            class="fin-btn-dark"
                            wire:click="$set('showDeleteModal', false)"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="fin-btn-red"
                            wire:click="deleteConfirmed"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
