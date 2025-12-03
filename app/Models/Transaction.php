<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'date',
        'title',
        'type',        // income / expense
        'category_id',
        'amount',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
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
     * Generate kode transaksi otomatis.
     */
    public static function generateCode(): string
    {
        do {
            $code = 'TRX-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
