<?php

namespace Tests\Unit;

use App\Models\Hospital;
use App\Models\OtpCode;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function hospital(): Hospital
    {
        return Hospital::create([
            'name' => 'RS OTP Unit',
            'slug' => 'rs-otp-unit',
            'pic_phone' => '081234567890',
            'is_active' => true,
        ]);
    }

    public function test_generate_returns_readable_code_and_hashes_it(): void
    {
        config(['hasnurverif.otp.channel' => 'log', 'hasnurverif.otp.length' => 6]);
        $service = new OtpService;
        $hospital = $this->hospital();

        $result = $service->generate($hospital, '6281234567890');

        $this->assertMatchesRegularExpression('/^\d{6}$/', $result['code']);
        $this->assertNotEquals($result['code'], OtpCode::latest()->first()->code_hash);
    }

    public function test_correct_code_verifies_and_is_single_use(): void
    {
        config(['hasnurverif.otp.channel' => 'log']);
        $service = new OtpService;
        $hospital = $this->hospital();
        $code = $service->generate($hospital, '6281234567890')['code'];

        $this->assertSame('ok', $service->verify($hospital, '6281234567890', $code));
        $this->assertSame('not_found', $service->verify($hospital, '6281234567890', $code));
    }

    public function test_wrong_code_is_invalid(): void
    {
        config(['hasnurverif.otp.channel' => 'log']);
        $service = new OtpService;
        $hospital = $this->hospital();
        $service->generate($hospital, '6281234567890');

        $this->assertSame('invalid', $service->verify($hospital, '6281234567890', '000000'));
    }

    public function test_expired_code_is_rejected(): void
    {
        config(['hasnurverif.otp.channel' => 'log']);
        $service = new OtpService;
        $hospital = $this->hospital();
        $code = $service->generate($hospital, '6281234567890')['code'];

        OtpCode::latest()->first()->update(['expires_at' => now()->subMinute()]);

        $this->assertSame('expired', $service->verify($hospital, '6281234567890', $code));
    }
}
