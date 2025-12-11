<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxWords implements ValidationRule
{
    public function __construct(
        protected int $maxWords = 50, // default 50 kata
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        // Normalisasi spasi
        $normalized = trim(preg_replace('/\s+/', ' ', $value));

        if ($normalized === '') {
            return; // kosong = lolos, karena field kamu nullable
        }

        // Hitung kata
        $wordCount = str_word_count($normalized);

        if ($wordCount > $this->maxWords) {
            $fail("Field :attribute maksimal {$this->maxWords} kata (sekarang {$wordCount} kata).");
        }
    }
}
