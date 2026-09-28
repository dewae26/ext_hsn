<?php

namespace App\Services\Otp;

use App\Models\Hospital;

interface OtpChannel
{
    /**
     * Kirim kode OTP ke nomor tujuan.
     *
     * @return bool true bila berhasil dikirim
     */
    public function send(Hospital $hospital, string $phone, string $code): bool;

    public function name(): string;
}
