<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FinanceReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * View Blade untuk page ini.
     */
    protected static string $view = 'filament.pages.finance-report';

    /**
     * Judul di header page.
     */
    protected static ?string $title = 'Laporan Keuangan';

    /**
     * Label di sidebar.
     */
    protected static ?string $navigationLabel = 'Laporan Keuangan';

    /**
     * Group di sidebar (optional).
     * Kalau mau digabung dengan menu lain, bisa diganti misal "Laporan & Analitik".
     */
    protected static ?string $navigationGroup = 'Laporan';

    /**
     * Icon di sidebar (heroicons).
     */
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    /**
     * URL path setelah /admin.
     * Contoh: /admin/finance-report
     */
    protected static ?string $slug = 'finance-report';

    /**
     * Urutan menu di sidebar (angka kecil = posisi lebih atas).
     */
    protected static ?int $navigationSort = 10;

    /**
     * Konfigurasi tabel di halaman Laporan.
     */
    public function table(Table $table): Table
    {
        return $table
            // Query dasar untuk ambil data transaksi + relasi kategori
            ->query(
                Transaction::query()
                    ->with('category')
            )

            // Kolom-kolom yang ditampilkan
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'income' ? 'Income' : 'Expense')
                    ->colors([
                        'success' => 'income',
                        'danger'  => 'expense',
                    ]),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('idr', true)
                    ->sortable()
                    // summary total di footer tabel
                    ->summarize([
                        Sum::make()
                            ->label('Total')
                            ->money('idr', true),
                    ]),
            ])

            // Filter-filter laporan
            ->filters([
                // Filter range tanggal
                Tables\Filters\Filter::make('date_range')
                    ->label('Rentang tanggal')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('Dari tanggal'),
                        DatePicker::make('date_to')
                            ->label('Sampai tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('date', '>=', $date)
                            )
                            ->when(
                                $data['date_to'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('date', '<=', $date)
                            );
                    }),

                // Filter berdasarkan kategori
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
            ])

            // opsi pagination (10, 25, 50, 100, semua)
            ->paginated([10, 25, 50, 100, 'all'])
            ->defaultSort('date', 'desc')
            ->searchPlaceholder('Cari deskripsi / kategori...');
    }
}
