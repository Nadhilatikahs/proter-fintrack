<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetGoal extends Model
{
    use HasFactory;

    protected $table = 'budget_goals';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'type',                  // 'budget' | 'goal'
        'period_type',
        'target_amount',
        'target_date',
        'last_notified_progress' // progress terakhir yang sudah dikirim notif
    ];

    protected $casts = [
        'target_date'            => 'date',
        'last_notified_progress' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /** Scope cepat untuk ambil semua budget */
    public function scopeBudgets($query)
    {
        return $query->where('type', 'budget');
    }

    /** Scope cepat untuk ambil semua goal */
    public function scopeGoals($query)
    {
        return $query->where('type', 'goal');
    }
}
