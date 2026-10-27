<?php

namespace App\Support;

/**
 * TOTP (RFC 6238, HMAC-SHA1, 30 detik, 6 digit) tanpa dependensi.
 * Kompatibel dengan Google Authenticator, Authy, 1Password, Aegis, dll.
 */
final class Totp
{
    public const PERIOD = 30;
    public const DIGITS = 6;

    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret(int $bytes = 20): string
    {
        return self::base32Encode(random_bytes($bytes));
    }

    public static function step(?int $time = null): int
    {
        return intdiv($time ?? time(), self::PERIOD);
    }

    public static function code(string $secret, int $step): string
    {
        $hash = hash_hmac('sha1', pack('J', $step), self::base32Decode($secret), true);
        $o = ord($hash[19]) & 0x0F;
        $bin = ((ord($hash[$o]) & 0x7F) << 24) | (ord($hash[$o + 1]) << 16)
            | (ord($hash[$o + 2]) << 8) | ord($hash[$o + 3]);

        return str_pad((string) ($bin % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Kembalikan step yang cocok, atau null. $afterStep mencegah replay: kode dari step
     * yang sudah pernah dipakai (<= $afterStep) ditolak. Semua offset dicek tanpa early-return.
     */
    public static function verify(string $secret, string $input, ?int $afterStep = null, int $window = 1, ?int $time = null): ?int
    {
        $input = (string) preg_replace('/\s+/', '', $input);
        if (! preg_match('/^\d{' . self::DIGITS . '}$/', $input)) {
            return null;
        }

        $now = self::step($time);
        $matched = null;
        for ($i = -$window; $i <= $window; $i++) {
            $s = $now + $i;
            if (hash_equals(self::code($secret, $s), $input) && ($afterStep === null || $s > $afterStep)) {
                $matched = $s;
            }
        }

        return $matched;
    }

    public static function uri(string $secret, string $account, string $issuer): string
    {
        return 'otpauth://totp/' . rawurlencode($issuer . ':' . $account)
            . '?secret=' . $secret . '&issuer=' . rawurlencode($issuer)
            . '&algorithm=SHA1&digits=' . self::DIGITS . '&period=' . self::PERIOD;
    }

    public static function base32Encode(string $bin): string
    {
        $bits = '';
        foreach (str_split($bin) as $c) {
            $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 5) as $chunk) {
            $out .= self::ALPHABET[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }

        return $out;
    }

    public static function base32Decode(string $b32): string
    {
        $bits = '';
        foreach (str_split(strtoupper(rtrim($b32, '='))) as $c) {
            $v = strpos(self::ALPHABET, $c);
            if ($v === false) {
                continue;
            }
            $bits .= str_pad(decbin($v), 5, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $out .= chr(bindec($byte));
            }
        }

        return $out;
    }
}
