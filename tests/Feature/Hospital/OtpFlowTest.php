<?php

namespace Tests\Feature\Hospital;

use App\Models\Hospital;
use App\Models\OtpCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function hospital(): Hospital
    {
        $hospital = Hospital::create([
            'name' => 'RS OTP',
            'slug' => 'rs-otp',
            'pic_phone' => '081234567890',
            'is_active' => true,
        ]);

        $hospital->contacts()->create([
            'label' => 'PIC',
            'phone' => '081234567890',
            'phone_normalized' => '6281234567890',
            'is_active' => true,
        ]);

        return $hospital;
    }

    public function test_request_otp_redirects_to_otp_page(): void
    {
        config(['hasnurverif.otp.enabled' => true, 'hasnurverif.otp.channel' => 'log']);
        $hospital = $this->hospital();

        $this->post(route('hospital.otp.request', $hospital->slug), ['phone' => '081234567890'])
            ->assertRedirect(route('hospital.otp.show', $hospital->slug));

        $this->assertGuest('hospital');
        $this->assertDatabaseCount('otp_codes', 1);
        $this->assertEquals('6281234567890', session('hospital_pending_phone'));
    }

    public function test_wrong_otp_is_rejected(): void
    {
        config(['hasnurverif.otp.enabled' => true, 'hasnurverif.otp.channel' => 'log']);
        $hospital = $this->hospital();

        $this->post(route('hospital.otp.request', $hospital->slug), ['phone' => '081234567890']);
        $otp = OtpCode::latest()->first();

        $this->post(route('hospital.otp.verify', $hospital->slug), ['code' => '000000'])
            ->assertSessionHas('error');

        $this->assertGuest('hospital');
        $this->assertGreaterThanOrEqual(1, $otp->fresh()->attempts);
    }
}
