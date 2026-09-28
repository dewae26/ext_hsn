@extends('layouts.guest')

@section('title', 'Login RS')

@section('content')
    <div class="text-center mb-4">
        <h5 class="fw-bold mb-1">{{ $hospital->name }}</h5>
        <p class="text-muted small mb-0">Masukkan nomor WhatsApp PIC yang terdaftar.</p>
    </div>

    <form method="POST" action="{{ route('hospital.otp.request', $hospital->slug) }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nomor WhatsApp PIC</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror"
                    placeholder="08xxxxxxxxxx" inputmode="tel" required autofocus>
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        @if (config('hasnurverif.otp.enabled'))
            <button class="btn btn-hv w-100 py-2 fw-semibold" type="submit">
                <i class="bi bi-shield-lock me-1"></i> Kirim Kode OTP
            </button>
        @else
            <button class="btn btn-hv w-100 py-2 fw-semibold" type="submit">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
            <div class="alert alert-warning small mt-3 mb-0">
                <i class="bi bi-info-circle me-1"></i> Verifikasi OTP WhatsApp belum aktif. Login langsung diizinkan untuk sementara.
            </div>
        @endif
    </form>
@endsection
