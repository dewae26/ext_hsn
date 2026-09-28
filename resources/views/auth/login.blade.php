@extends('layouts.guest')

@section('title', 'Masuk Admin')

@section('content')
    <div class="text-center mb-4">
        <h5 class="fw-bold mb-1">Masuk Admin</h5>
        <p class="text-muted small mb-0">Masuk menggunakan akun SSO Hasnur Group.</p>
    </div>

    <div class="d-grid">
        <a href="{{ route('sso.redirect') }}" class="btn btn-sso py-2 fw-semibold">
            <i class="bi bi-box-arrow-in-right me-2"></i> Masuk dengan SSO Hasnur Group
        </a>
    </div>

    <p class="text-center text-muted small mt-4 mb-0">
        Hanya NRP yang terdaftar sebagai admin yang dapat mengakses sistem ini.
    </p>
@endsection
