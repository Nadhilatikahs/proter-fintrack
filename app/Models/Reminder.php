<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'related_id',
        'related_model',
        'title',
        'message',
        'data',
        'sent_at',
        'is_read',
    ];

    protected $casts = [
        'data'    => 'array',
        'sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function related()
    {
        // kalau kamu mau morphTo ke Budget/Goal, ini opsional
        return $this->morphTo(null, 'related_model', 'related_id');
    }
}
