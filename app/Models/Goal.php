<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'current_amount',
        'target_date',
        'category_id',
        'notes',
    ];

    protected $casts = [
        'target_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->target_amount <= 0) {
            return 0;
        }

        return round(($this->current_amount / $this->target_amount) * 100, 2);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->current_amount >= $this->target_amount;
    }
}
