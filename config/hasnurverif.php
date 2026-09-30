<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin SSO
    |--------------------------------------------------------------------------
    |
    | Hanya NRP yang terdaftar pada tabel `users` yang boleh login sebagai
    | admin melalui SSO Hasnur Group.
    |
    */

    'admin' => [
        'whitelist_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP (Verifikasi Rumah Sakit)
    |--------------------------------------------------------------------------
    */

    'otp' => [
        'enabled' => env('OTP_ENABLED', false),
        'length' => (int) env('OTP_LENGTH', 6),
        'ttl_minutes' => (int) env('OTP_TTL_MINUTES', 5),
        'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
        'resend_cooldown_seconds' => (int) env('OTP_RESEND_COOLDOWN_SECONDS', 60),
        'channel' => env('OTP_CHANNEL', 'log'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limit' => [
        'lookup_per_minute' => (int) env('RATE_LIMIT_LOOKUP_PER_MINUTE', 30),
        'otp_request_per_15_minutes' => (int) env('RATE_LIMIT_OTP_REQUEST', 3),
        'otp_verify_per_15_minutes' => (int) env('RATE_LIMIT_OTP_VERIFY', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Nominal Kamar per Malam (berdasarkan job_level karyawan)
    |--------------------------------------------------------------------------
    */

    'room_rate' => [
        'high_levels' => ['Director', 'Deputy Director', 'Senior Manager', 'Manager'],
        'high' => 967000,
        'regular' => 676000,
    ],

    /*
    |--------------------------------------------------------------------------
    | PKS (Perjanjian Kerja Sama)
    |--------------------------------------------------------------------------
    */

    'pks' => [
        'disk' => 'local',
        'max_size_kb' => 5120,
        'mimes' => ['application/pdf'],
    ],

];
