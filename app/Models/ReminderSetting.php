<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'budget_warning_threshold',
        'goal_days_before_due',
        'daily_digest_hour',
        'notify_email',
        'notify_in_app',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
