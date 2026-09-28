<?php

namespace App\Services;

use App\Models\Hospital;
use App\Models\OtpCode;
use App\Services\Otp\LogOtpChannel;
use App\Services\Otp\OtpChannel;
use App\Services\Otp\WhatsAppOtpChannel;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    public function enabled(): bool
    {
        return (bool) config('hasnurverif.otp.enabled');
    }

    public function channel(): OtpChannel
    {
        return match (config('hasnurverif.otp.channel')) {
            'whatsapp' => new WhatsAppOtpChannel,
            default => new LogOtpChannel,
        };
    }

    /**
     * Buat & kirim OTP baru. Mengembalikan kode (untuk ditampilkan di mode dev).
     *
     * @return array{code: string, sent: bool}
     */
    public function generate(Hospital $hospital, string $phone, ?string $ip = null): array
    {
        // Batalkan OTP lama yang belum dipakai
        OtpCode::where('hospital_id', $hospital->id)
            ->where('phone', $phone)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        $length = (int) config('hasnurverif.otp.length', 6);
        $max = (10 ** $length) - 1;
        $code = str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);

        OtpCode::create([
            'hospital_id' => $hospital->id,
            'phone' => $phone,
            'code_hash' => Hash::make($code),
            'purpose' => 'login',
            'expires_at' => now()->addMinutes((int) config('hasnurverif.otp.ttl_minutes', 5)),
            'ip' => $ip,
        ]);

        $sent = $this->channel()->send($hospital, $phone, $code);

        return ['code' => $code, 'sent' => $sent];
    }

    /**
     * Verifikasi kode OTP.
     *
     * @return string ok|not_found|expired|too_many|invalid
     */
    public function verify(Hospital $hospital, string $phone, string $code): string
    {
        $otp = OtpCode::where('hospital_id', $hospital->id)
            ->where('phone', $phone)
            ->whereNull('consumed_at')
            ->latest()
            ->first();

        if (! $otp) {
            return 'not_found';
        }

        if ($otp->isExpired()) {
            return 'expired';
        }

        if ($otp->attempts >= (int) config('hasnurverif.otp.max_attempts', 5)) {
            return 'too_many';
        }

        $otp->increment('attempts');

        if (! Hash::check($code, $otp->code_hash)) {
            return 'invalid';
        }

        $otp->update(['consumed_at' => now()]);

        return 'ok';
    }
}
