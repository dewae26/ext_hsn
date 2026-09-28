@extends('layouts.guest')

@section('title', 'Link Tidak Aktif')

@section('content')
    <div class="text-center py-3">
        <i class="bi bi-slash-circle text-danger" style="font-size:3rem;"></i>
        <h5 class="fw-semibold mt-3">Link Tidak Aktif</h5>
        <p class="text-muted mb-0">
            Akses untuk <strong>{{ $hospital->name }}</strong> sedang dinonaktifkan.
            Silakan hubungi admin Hasnur Group.
        </p>
    </div>
@endsection
