<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'date',
        'description',
        'category_id',
        'amount',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

