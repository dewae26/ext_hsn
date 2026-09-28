<?php

namespace App\Services\Otp;

use App\Models\Hospital;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Channel OTP via WhatsApp API.
 *
 * BELUM AKTIF. Endpoint & payload menyesuaikan gateway yang akan dipilih
 * (mis. Fonnte/Wablas/Qontak/Twilio). Aktifkan dengan WHATSAPP_ENABLED=true
 * dan isi WHATSAPP_BASE_URL/WHATSAPP_TOKEN/WHATSAPP_SENDER.
 */
class WhatsAppOtpChannel implements OtpChannel
{
    public function send(Hospital $hospital, string $phone, string $code): bool
    {
        if (! config('services.whatsapp.enabled')) {
            Log::warning('[OTP-WA] WhatsApp channel belum aktif; kode tidak terkirim', [
                'phone' => $phone,
            ]);

            return false;
        }

        try {
            $response = Http::withToken(config('services.whatsapp.token'))
                ->timeout(15)
                ->post(config('services.whatsapp.base_url'), [
                    'target' => $phone,
                    'sender' => config('services.whatsapp.sender'),
                    'message' => "Kode OTP HasnurVerif Anda: {$code}. Berlaku ".config('hasnurverif.otp.ttl_minutes').' menit. Jangan bagikan kode ini.',
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('[OTP-WA] Gagal kirim OTP', ['message' => $e->getMessage()]);

            return false;
        }
    }

    public function name(): string
    {
        return 'whatsapp';
    }
}
