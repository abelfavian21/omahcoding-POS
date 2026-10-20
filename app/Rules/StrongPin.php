<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** PIN kasir baru: 6 digit, bukan pola mudah (000000, 123456, 121212, ...). */
class StrongPin implements ValidationRule
{
    public const LENGTH = 6;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $v = (string) $value;

        if (! preg_match('/^\d{' . self::LENGTH . '}$/', $v)) {
            $fail('PIN harus tepat ' . self::LENGTH . ' digit angka.');
            return;
        }

        $asc = '01234567890123456789';
        $desc = strrev($asc);

        if (preg_match('/^(\d)\1+$/', $v)                // 111111
            || str_contains($asc, $v) || str_contains($desc, $v)   // 123456 / 654321
            || preg_match('/^(\d{2})\1\1$/', $v)         // 121212
            || preg_match('/^(\d{3})\1$/', $v)) {        // 123123
            $fail('PIN terlalu mudah ditebak. Hindari angka berulang atau berurutan.');
        }
    }
}
