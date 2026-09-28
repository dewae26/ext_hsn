<?php

namespace App\Services\Otp;

use App\Models\Hospital;
use Illuminate\Support\Facades\Log;

/**
 * Channel OTP untuk pengembangan: kode ditulis ke log aplikasi.
 * Tidak mengirim pesan sungguhan.
 */
class LogOtpChannel implements OtpChannel
{
    public function send(Hospital $hospital, string $phone, string $code): bool
    {
        Log::info('[OTP-DEV] Kode OTP dikirim', [
            'hospital' => $hospital->name,
            'phone' => $phone,
            'code' => $code,
        ]);

        return true;
    }

    public function name(): string
    {
        return 'log';
    }
}
