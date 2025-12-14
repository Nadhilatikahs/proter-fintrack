@extends('layouts.fintrack')

@section('title','Categories')

@section('top-left')
    <div class="ft-heading">Categories</div>
    <div class="ft-subheading">Kelompokkan transaksi biar rapi</div>
@endsection

@section('content')
<div class="ft-cat-layout">
    <div class="ft-cat-container">

        {{-- HEADER --}}
        <div class="ft-cat-header">
            <h1 class="ft-page-title">Categories</h1>

            <a href="{{ route('categories.create') }}"
               class="ft-btn-outline-green">
                + Input Categories
            </a>
        </div>

        {{-- TABLE HEAD --}}
        <div class="ft-cat-table-head">
            <div>Date</div>
            <div>Category</div>
            <div class="text-right">Actions</div>
        </div>

        {{-- LIST --}}
        <div class="ft-cat-list">
            @forelse ($categories as $cat)
                <div class="ft-cat-row">

                    {{-- DATE --}}
                    <div class="ft-cat-date">
                        {{ $cat->created_at->format('d F Y') }}
                    </div>

                    {{-- CATEGORY --}}
                    <div class="ft-cat-name">
                        <span class="ft-pill ft-pill-category">
                            {{ $cat->name }}
                        </span>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="ft-cat-actions">
                        <a href="{{ route('categories.edit',$cat) }}"
                           class="fin-btn-dark">
                            Edit
                        </a>

                        <form method="POST"
                              action="{{ route('categories.destroy',$cat) }}"
                              onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">
                            @csrf
                            @method('DELETE')
                            <button class="fin-btn-red" type="submit">
                                Delete
                            </button>
                        </form>
                    </div>

                </div>
            @empty
                <div class="ft-empty-text">
                    Belum ada kategori. Coba buat kategori Food, Lifestyle, dll dulu.
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection
