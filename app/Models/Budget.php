<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'month',
        'year',
        'limit_amount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Hitung total pengeluaran (expense) untuk budget ini.
     */
    public function getSpentAttribute(): float
    {
        if (! $this->category_id) {
            return 0;
        }

        return $this->category
            ?->transactions()
            ->where('user_id', $this->user_id)
            ->whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->whereHas('category', fn ($q) => $q->where('type', 'expense'))
            ->sum('amount') ?? 0;
    }

    public function getRemainingAttribute(): float
    {
        return $this->limit_amount - $this->spent;
    }

    public function getUsagePercentageAttribute(): float
    {
        if ($this->limit_amount <= 0) {
            return 0;
        }

        return round(($this->spent / $this->limit_amount) * 100, 2);
    }
}
