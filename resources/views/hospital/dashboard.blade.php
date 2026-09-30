@extends('layouts.hospital')

@section('title', 'Dashboard RS')

@section('header-action')
    <a href="{{ route('hospital.logout', $hospital->slug) }}" class="btn btn-sm btn-light">
        <i class="bi bi-box-arrow-right me-1"></i> Logout
    </a>
@endsection

@section('content')
    <div class="mb-3">
        <h4 class="mb-0 fw-bold">{{ $hospital->name }}</h4>
        <div class="text-muted small">Verifikasi status karyawan Hasnur Group</div>
    </div>

    <div class="card hv-card mb-3">
        <div class="card-header bg-white">
            <span class="fw-bold"><i class="bi bi-person-check text-hv me-1"></i> Cek Status Karyawan</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <form method="POST" action="{{ route('hospital.lookup', $hospital->slug) }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-8">
                            <label class="form-label">NRP Karyawan</label>
                            <input type="text" name="nrp" value="{{ old('nrp') }}"
                                class="form-control @error('nrp') is-invalid @enderror"
                                placeholder="Masukkan NRP" required autofocus>
                            @error('nrp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-4">
                            <button class="btn btn-hv w-100" type="submit">
                                <i class="bi bi-search me-1"></i> Cek
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($result && $result['result'] !== 'not_found')
        {{-- Detail karyawan disembunyikan otomatis setelah 2 menit (120 detik) --}}
        <div class="card hv-card" id="resultCard">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="fw-bold">Hasil Verifikasi</span>
                <span class="small text-muted">
                    Menyembunyikan dalam
                    <span class="fw-bold text-danger" id="resultCountdown">120</span> detik
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="resultCloseBtn">
                        Tutup
                    </button>
                </span>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <span class="badge {{ $result['result'] === 'active' ? 'hv-status-active' : 'hv-status-inactive' }}">
                        @if ($result['result'] === 'active')
                            <i class="bi bi-check-circle me-1"></i> VALID
                        @else
                            <i class="bi bi-x-circle me-1"></i> TIDAK VALID
                        @endif
                    </span>
                </div>
                <table class="table table-sm align-middle hv-detail mb-0">
                    <tbody>
                        <tr>
                            <th scope="row">NRP</th>
                            <td class="hv-detail-colon">:</td>
                            <td>{{ $result['nrp'] }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Nama Karyawan</th>
                            <td class="hv-detail-colon">:</td>
                            <td>{{ $result['employee_name'] }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Entitas</th>
                            <td class="hv-detail-colon">:</td>
                            <td>{{ $result['group_company'] }}</td>
                        </tr>
                        <tr>
                            <th scope="row">PT</th>
                            <td class="hv-detail-colon">:</td>
                            <td>{{ $result['company_name'] }}</td>
                        </tr>
                        <tr>
                            <th scope="row">KTP</th>
                            <td class="hv-detail-colon">:</td>
                            <td>{{ $result['ktp'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Nominal Kamar / Malam</th>
                            <td class="hv-detail-colon">:</td>
                            <td>
                                @if (! empty($result['room_rate']))
                                    Rp {{ number_format($result['room_rate'], 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>

                <hr>

                <div class="small text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Jika status karyawan <strong>on hold / on month notice</strong>, maka fasilitas
                    <strong>cashless nonaktif</strong>.
                </div>
            </div>
        </div>
    @elseif ($result)
        <div class="card hv-card">
            <div class="card-body">
                <div class="alert alert-secondary mb-0">
                    <i class="bi bi-question-circle me-1"></i> {{ $result['feedback'] }}
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const card = document.getElementById('resultCard');
            if (!card) return;

            const counter = document.getElementById('resultCountdown');
            const closeBtn = document.getElementById('resultCloseBtn');
            let seconds = 120;

            const timer = setInterval(function () {
                seconds--;
                if (counter) counter.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(timer);
                    card.style.display = 'none';
                }
            }, 1000);

            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    clearInterval(timer);
                    card.style.display = 'none';
                });
            }
        });
    </script>
@endpush
