<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class NoSpaces implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Str::contains($value, ' ')) {
            // Jika nilai input mengandung spasi, gagalkan validasi.
            $fail('Kolom :attribute tidak boleh mengandung spasi.');
        }
    }
}