@extends('layouts.fintrack')

@section('title','Categories')

@section('top-left')
    <div class="ft-heading">Categories</div>
    <div class="ft-subheading">Kelompokkan transaksi biar rapi</div>
@endsection

@section('content')
    <div class="ft-page-header-row">
        <a href="{{ route('categories.create') }}" class="ft-btn ft-btn-primary">
            + Add new category
        </a>
    </div>

    <div class="ft-budget-grid">
        @forelse($categories as $cat)
            <div class="ft-card ft-budget-card">
                <div class="ft-card-header">
                    <div>
                        <div class="ft-budget-name">{{ $cat->name }}</div>
                        <div class="ft-budget-meta">{{ $cat->description }}</div>
                    </div>
                    <div class="ft-budget-actions">
                        <a href="{{ route('categories.edit',$cat) }}" class="ft-link-sm">Edit</a>
                        <form action="{{ route('categories.destroy',$cat) }}" method="POST"
                              onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">
                            @csrf
                            @method('DELETE')
                            <button class="ft-link-sm ft-text-expense" type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <p>Belum ada kategori. Coba buat kategori Food, Lifestyle, dll dulu 😊</p>
        @endforelse
    </div>
@endsection
