<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\HospitalLoginLog;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(protected OtpService $otp) {}

    public function showLogin(Request $request)
    {
        $hospital = $request->attributes->get('hospital');

        if (Auth::guard('hospital')->check() && Auth::guard('hospital')->id() === $hospital->id) {
            return redirect()->route('hospital.dashboard', ['hospital' => $hospital->slug]);
        }

        return view('hospital.login', ['hospital' => $hospital]);
    }

    public function requestOtp(Request $request)
    {
        $hospital = $request->attributes->get('hospital');

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:25'],
        ]);

        $phone = Hospital::normalizePhone($validated['phone']);

        $contact = $phone
            ? $hospital->contacts()->active()->where('phone_normalized', $phone)->first()
            : null;

        if (! $contact) {
            $this->log($hospital, $validated['phone'], 'failed', 'nomor_tidak_terdaftar', $request);

            return back()->withInput()->with('error', 'Nomor tidak terdaftar untuk RS ini.');
        }

        // Simpan info PIC yang login untuk dicatat di setiap log verifikasi.
        $request->session()->put('hospital_contact_id', $contact->id);
        $request->session()->put('hospital_pic_phone', $contact->phone);
        $request->session()->put('hospital_pic_label', $contact->label);

        // Mode dev: OTP dimatikan -> langsung login (didokumentasikan di PRD)
        if (! $this->otp->enabled()) {
            $this->log($hospital, $phone, 'success', 'otp_disabled', $request);
            $this->login($request, $hospital);

            return redirect()->route('hospital.dashboard', ['hospital' => $hospital->slug]);
        }

        $result = $this->otp->generate($hospital, $phone, $request->ip());
        $request->session()->put('hospital_pending_phone', $phone);

        if (app()->environment('local') && $this->otp->channel()->name() === 'log') {
            session()->flash('dev_otp', $result['code']);
        }

        return redirect()->route('hospital.otp.show', ['hospital' => $hospital->slug])
            ->with('success', 'Kode OTP telah dikirim ke nomor PIC.');
    }

    public function showOtp(Request $request)
    {
        $hospital = $request->attributes->get('hospital');

        if (! $request->session()->has('hospital_pending_phone')) {
            return redirect()->route('hospital.login', ['hospital' => $hospital->slug]);
        }

        return view('hospital.otp', ['hospital' => $hospital]);
    }

    public function verifyOtp(Request $request)
    {
        $hospital = $request->attributes->get('hospital');

        $validated = $request->validate([
            'code' => ['required', 'digits:'.config('hasnurverif.otp.length', 6)],
        ]);

        $phone = $request->session()->get('hospital_pending_phone');

        if (! $phone) {
            return redirect()->route('hospital.login', ['hospital' => $hospital->slug]);
        }

        $status = $this->otp->verify($hospital, $phone, $validated['code']);

        if ($status !== 'ok') {
            $this->log($hospital, $phone, 'failed', 'otp_'.$status, $request);

            return back()->with('error', 'Kode OTP tidak valid atau sudah kedaluwarsa.');
        }

        $request->session()->forget('hospital_pending_phone');
        $this->log($hospital, $phone, 'success', 'otp_verified', $request);
        $this->login($request, $hospital);

        return redirect()->route('hospital.dashboard', ['hospital' => $hospital->slug]);
    }

    public function logout(Request $request)
    {
        $hospital = $request->attributes->get('hospital');

        Auth::guard('hospital')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('hospital.login', ['hospital' => $hospital->slug]);
    }

    protected function login(Request $request, Hospital $hospital): void
    {
        Auth::guard('hospital')->login($hospital);
        $request->session()->regenerate();
    }

    protected function log(Hospital $hospital, ?string $phone, string $status, string $reason, Request $request): void
    {
        $normalized = Hospital::normalizePhone($phone);
        $contact = $normalized
            ? $hospital->contacts()->where('phone_normalized', $normalized)->first()
            : null;

        HospitalLoginLog::create([
            'hospital_id' => $hospital->id,
            'hospital_name' => $hospital->name,
            'phone' => $contact?->phone ?? $phone,
            'pic_label' => $contact?->label,
            'status' => $status,
            'reason' => $reason,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
    }
}
