<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SsoController extends Controller
{
    /**
     * Arahkan user ke halaman login SSO Hasnur Group.
     */
    public function redirect()
    {
        $loginUrl = config('services.sso.login_url');

        if (! $loginUrl) {
            return redirect()->route('login')
                ->with('error', 'Konfigurasi SSO belum lengkap. Hubungi administrator.');
        }

        return redirect()->away($loginUrl);
    }

    /**
     * Terima callback dari SSO, verifikasi token, lalu login bila NRP terdaftar.
     */
    public function callback(Request $request)
    {
        $sessionId = $request->input('session_id');
        $ssoToken = $request->input('sso_token');

        if (! $sessionId || ! $ssoToken) {
            return redirect()->route('login')
                ->with('error', 'Data autentikasi dari SSO tidak lengkap.');
        }

        $verifyUrl = config('services.sso.verify_url');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$ssoToken,
                'X-App-Secret' => config('services.sso.app_secret'),
            ])->timeout(15)->post($verifyUrl, [
                'session_id' => $sessionId,
            ]);

            if (! $response->successful() || ! $response->json('valid')) {
                $this->logFailed($request, 'token_invalid');

                return redirect()->route('login')
                    ->with('error', 'Token SSO tidak valid atau sudah kedaluwarsa.');
            }

            $ssoUser = $response->json('user');
        } catch (\Throwable $e) {
            Log::error('SSO verify gagal', ['message' => $e->getMessage()]);

            return redirect()->route('login')
                ->with('error', 'Gagal terhubung ke server SSO untuk verifikasi.');
        }

        $nrp = $ssoUser['nrp'] ?? null;

        if (! $nrp) {
            return redirect()->route('login')
                ->with('error', 'Data user dari SSO tidak lengkap.');
        }

        $user = User::where('employee_id', $nrp)->first();

        if (! $user) {
            Log::warning('SSO login ditolak: NRP tidak ada di whitelist', ['nrp' => $nrp]);

            return redirect()->route('login')
                ->with('error', 'Akun Anda tidak memiliki akses ke sistem ini.');
        }

        if (! $user->is_active) {
            Log::warning('SSO login ditolak: akun tidak aktif', ['nrp' => $nrp]);

            return redirect()->route('login')
                ->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        // Sinkronkan data profil dasar dari SSO
        $user->forceFill([
            'name' => $ssoUser['name'] ?? $user->name,
            'email' => $ssoUser['email'] ?? $user->email,
            'last_login_at' => now(),
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    protected function logFailed(Request $request, string $reason): void
    {
        Log::warning('SSO callback gagal', [
            'reason' => $reason,
            'ip' => $request->ip(),
        ]);
    }
}
