<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    private const COMMON = [
        'password', 'password1', 'password123', 'passw0rd', 'qwerty123', 'qwertyuiop', '1234567890',
        'abc123456', 'iloveyou1', 'admin12345', 'kasir12345', 'cashierstore', 'welcome123', 'letmein123',
        'indonesia123', 'bismillah123', 'sayangku123', 'rahasia123',
    ];

    public function __construct(private ?string $email = null, private ?string $name = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $v = (string) $value;
        $lower = mb_strtolower($v);

        if (mb_strlen($v) < 10) {
            $fail('Kata sandi minimal 10 karakter.');
        } elseif (! preg_match('/[A-Za-z]/', $v) || ! preg_match('/\d/', $v)) {
            $fail('Kata sandi harus mengandung huruf dan angka.');
        } elseif (in_array($lower, self::COMMON, true) || preg_match('/^(.)\1+$/u', $v)) {
            $fail('Kata sandi terlalu mudah ditebak.');
        } elseif ($this->email && $lower === mb_strtolower($this->email)) {
            $fail('Kata sandi tidak boleh sama dengan email.');
        } elseif ($this->name && mb_strlen($this->name) >= 4 && str_contains($lower, mb_strtolower($this->name))) {
            $fail('Kata sandi tidak boleh memuat nama Anda.');
        }
    }
}
