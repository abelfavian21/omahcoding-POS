<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 2FA panel platform wajib di produksi; tes lama masuk panel tanpa 2FA.
        // PlatformTwoFactorTest menyalakannya lagi di setUp miliknya.
        config(['platform.require_2fa' => false]);
    }
}
