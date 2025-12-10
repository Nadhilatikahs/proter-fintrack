{{-- resources/views/transactions/index.blade.php --}}
@extends('layouts.fintrack')

@section('title','Transactions')

@section('top-left')
    <div class="ft-heading">Transactions</div>
    <div class="ft-subheading">Kelola catatan pemasukan & pengeluaranmu</div>
@endsection

@section('content')
    <div class="ft-page-header-row">
        <div class="ft-segmented">
            @foreach(['all' => 'All', 'day' => 'Day', 'month' => 'Month', 'category' => 'Categories'] as $key => $label)
                <a href="{{ route('transactions.index', ['scope' => $key]) }}"
                   class="ft-seg-btn {{ ($scope ?? 'all') === $key ? 'is-active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <a href="{{ route('transactions.create') }}" class="ft-btn ft-btn-primary">
            + Input Transaction
        </a>
    </div>

    <div class="ft-card">
        @if($transactions->isEmpty())
            <p>Belum ada transaksi sesuai filter.</p>
        @else
            <ul class="ft-tx-list">
                @foreach($transactions as $tx)
                    <li class="ft-tx-row">
                        <div>
                            <div class="ft-tx-name">{{ $tx->description ?? $tx->code ?? 'Transaksi' }}</div>
                            <div class="ft-tx-meta">
                                {{ optional($tx->category)->name ?? '-' }} • {{ $tx->date->format('d M Y') }}
                            </div>
                        </div>
                        <div class="ft-tx-amount {{ $tx->type === 'income' ? 'ft-text-income' : 'ft-text-expense' }}">
                            {{ $tx->type === 'income' ? '+' : '-' }}
                            Rp {{ number_format($tx->amount,0,'.',',') }}
                        </div>
                        <div class="ft-tx-actions">
                            <a href="{{ route('transactions.edit',$tx) }}" class="ft-link-sm">Edit</a>
                            <form action="{{ route('transactions.destroy',$tx) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus transaksi ini?');">
                                @csrf
                                @method('DELETE')
                                <button class="ft-link-sm ft-text-expense" type="submit">Delete</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
