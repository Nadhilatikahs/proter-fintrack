<?php

namespace App\Filament\Widgets;

use App\Models\BudgetGoal;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BudgetStatusWidget extends BaseWidget
{
    protected static ?int $sort = 2; // tampil setelah overview

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getBaseQuery())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama budget')
                    ->wrap()
                    ->sortable(),

                Tables\Columns\TextColumn::make('period_type')
                    ->label('Periode')
                    ->formatStateUsing(fn ($state) => $this->formatPeriod($state)),

                Tables\Columns\TextColumn::make('target_amount')
                    ->label('Batas nominal')
                    ->money('idr', true),

                Tables\Columns\TextColumn::make('spent_label')
                    ->label('Sudah terpakai')
                    ->getStateUsing(fn (BudgetGoal $record) =>
                        'Rp ' . number_format($this->calculateSpent($record), 0, '.', ',')
                    ),

                // ⬇️ Ganti ProgressColumn -> TextColumn
                Tables\Columns\TextColumn::make('usage_percentage')
                    ->label('Pemakaian')
                    ->getStateUsing(fn (BudgetGoal $record) =>
                        $this->calculateUsage($record) . ' %'
                    )
                    ->color(function (BudgetGoal $record) {
                        $value = $this->calculateUsage($record);

                        if ($value < 50) {
                            return 'success';
                        }

                        if ($value < 90) {
                            return 'warning';
                        }

                        return 'danger';
                    }),
            ])
            ->defaultSort('name')
            ->paginated(false); // tampilkan semua budget aktif
    }

    protected function getBaseQuery(): Builder
    {
        return BudgetGoal::query()
            ->where('user_id', Auth::id())
            ->where('type', 'budget')
            ->whereNotNull('period_type');
    }

    protected function formatPeriod(?string $periodType): string
    {
        return match ($periodType) {
            'daily'    => 'Per hari',
            'weekly'   => 'Per minggu',
            'biweekly' => 'Per 2 minggu',
            'monthly'  => 'Per bulan',
            'yearly'   => 'Per tahun',
            default    => '-',
        };
    }

    /**
     * Hitung total pengeluaran untuk budget ini sesuai period_type.
     */
    protected function calculateSpent(BudgetGoal $budget): float
    {
        $userId = $budget->user_id;
        $now    = Carbon::now();

        [$start, $end] = $this->getPeriodRange($budget->period_type, $now);

        return Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');
    }

    /**
     * Hitung persentase pemakaian budget.
     */
    protected function calculateUsage(BudgetGoal $budget): float
    {
        if ($budget->target_amount <= 0) {
            return 0;
        }

        $spent = $this->calculateSpent($budget);

        return round(($spent / $budget->target_amount) * 100, 2);
    }

    /**
     * Daur ulang logika periode seperti di command reminder.
     *
     * @return array [Carbon $start, Carbon $end]
     */
    protected function getPeriodRange(string $periodType, Carbon $now): array
    {
        $start = $now->copy();
        $end   = $now->copy();

        return match ($periodType) {
            'daily'    => [
                $start->startOfDay(),
                $end->endOfDay(),
            ],
            'weekly'   => [
                $start->startOfWeek(),
                $end->endOfWeek(),
            ],
            'biweekly' => [
                $start->copy()->subDays(13)->startOfDay(), // 14 hari terakhir
                $end->endOfDay(),
            ],
            'yearly'   => [
                $start->startOfYear(),
                $end->endOfYear(),
            ],
            default    => [
                // default: monthly
                $start->startOfMonth(),
                $end->endOfMonth(),
            ],
        };
    }
}
