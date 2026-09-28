@extends('layouts.guest')

@section('title', 'Verifikasi OTP')

@section('content')
    <div class="text-center mb-4">
        <h5 class="fw-semibold mb-1">Verifikasi OTP</h5>
        <p class="text-muted small mb-0">{{ $hospital->name }}</p>
    </div>

    @if (session('dev_otp'))
        <div class="alert alert-info small">
            <i class="bi bi-bug me-1"></i> <strong>Mode Dev:</strong> kode OTP Anda
            <span class="fw-bold">{{ session('dev_otp') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('hospital.otp.verify', $hospital->slug) }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Kode OTP ({{ config('hasnurverif.otp.length') }} angka)</label>
            <input type="text" name="code" class="form-control text-center fs-4 @error('code') is-invalid @enderror"
                inputmode="numeric" maxlength="{{ config('hasnurverif.otp.length') }}" placeholder="______"
                autofocus autocomplete="one-time-code" required>
            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button class="btn btn-hv w-100 py-2 fw-semibold" type="submit">
            <i class="bi bi-check2-circle me-1"></i> Verifikasi &amp; Masuk
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="{{ route('hospital.login', $hospital->slug) }}" class="small">Kembali</a>
    </div>
@endsection
