<?php

namespace Tests\Unit;

use App\Support\Totp;
use PHPUnit\Framework\TestCase;

class TotpTest extends TestCase
{
    private const SECRET = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';   // "12345678901234567890" (vektor RFC 6238)

    public function test_vektor_rfc6238(): void
    {
        foreach ([59 => '287082', 1111111109 => '081804', 1111111111 => '050471', 1234567890 => '005924', 2000000000 => '279037', 20000000000 => '353130'] as $t => $code) {
            $this->assertSame($code, Totp::code(self::SECRET, Totp::step($t)), "t=$t");
        }
    }

    public function test_base32_bolak_balik(): void
    {
        $raw = random_bytes(20);
        $this->assertSame($raw, Totp::base32Decode(Totp::base32Encode($raw)));
        $this->assertSame('12345678901234567890', Totp::base32Decode(self::SECRET));
    }

    public function test_verify_menerima_jendela_plus_minus_satu_dan_menolak_lainnya(): void
    {
        $t = 1111111111;
        $step = Totp::step($t);
        $this->assertSame($step, Totp::verify(self::SECRET, Totp::code(self::SECRET, $step), null, 1, $t));
        $this->assertSame($step - 1, Totp::verify(self::SECRET, Totp::code(self::SECRET, $step - 1), null, 1, $t));
        $this->assertNull(Totp::verify(self::SECRET, Totp::code(self::SECRET, $step + 2), null, 1, $t));
        $this->assertNull(Totp::verify(self::SECRET, '12345', null, 1, $t));
        $this->assertNull(Totp::verify(self::SECRET, 'abcdef', null, 1, $t));
    }

    public function test_replay_ditolak(): void
    {
        $t = 1111111111;
        $step = Totp::step($t);
        $code = Totp::code(self::SECRET, $step);
        $this->assertSame($step, Totp::verify(self::SECRET, $code, $step - 1, 1, $t));
        $this->assertNull(Totp::verify(self::SECRET, $code, $step, 1, $t));
    }

    public function test_spasi_pada_kode_diabaikan(): void
    {
        $t = 59;
        $c = Totp::code(self::SECRET, Totp::step($t));
        $this->assertNotNull(Totp::verify(self::SECRET, substr($c, 0, 3) . ' ' . substr($c, 3), null, 1, $t));
    }
}
