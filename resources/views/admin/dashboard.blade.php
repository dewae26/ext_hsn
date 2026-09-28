@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h4 class="mb-4 fw-bold">Dashboard</h4>

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card hv-card h-100">
                <div class="card-body">
                    <div class="text-muted small">Total Rumah Sakit</div>
                    <div class="fs-3 fw-bold text-hv">{{ $stats['hospitals_total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card hv-card h-100">
                <div class="card-body">
                    <div class="text-muted small">RS Aktif</div>
                    <div class="fs-3 fw-bold text-success">{{ $stats['hospitals_active'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card hv-card h-100">
                <div class="card-body">
                    <div class="text-muted small">Login RS Hari Ini</div>
                    <div class="fs-3 fw-bold text-hv">{{ $stats['logins_today'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card hv-card h-100">
                <div class="card-body">
                    <div class="text-muted small">Pengecekan NRP Hari Ini</div>
                    <div class="fs-3 fw-bold text-hv">{{ $stats['lookups_today'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card hv-card h-100">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span class="fw-bold">Pengecekan NRP per Rumah Sakit</span>
                        <div class="text-muted small">{{ $chartTotal }} pengecekan dalam {{ $days }} hari terakhir</div>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        @foreach ([7, 14, 30] as $option)
                            <a href="{{ route('admin.dashboard', ['days' => $option]) }}"
                                class="btn {{ $days === $option ? 'btn-hv' : 'btn-outline-secondary' }}">{{ $option }} hari</a>
                        @endforeach
                    </div>
                </div>
                <div class="card-body">
                    @if (count($chartDatasets))
                        <div style="height: 320px;">
                            <canvas id="verificationChart"></canvas>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-bar-chart fs-2 d-block mb-2"></i>
                            Belum ada pengecekan NRP pada periode ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card hv-card h-100">
                <div class="card-header bg-white">
                    <span class="fw-bold">Paling Sering Cek ({{ $days }} hari)</span>
                </div>
                <div class="card-body">
                    @forelse ($ranking as $i => $item)
                        @php($percent = $ranking[0]['total'] > 0 ? round($item['total'] / $ranking[0]['total'] * 100) : 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold text-truncate" style="max-width: 70%;">
                                    {{ $i + 1 }}. {{ $item['name'] }}
                                </span>
                                <span class="badge bg-light text-dark">{{ $item['total'] }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: {{ $percent }}%; background-color: {{ $item['color'] ?? '#001F95' }};"
                                    aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">Belum ada data.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card hv-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Pengecekan Terbaru</span>
                    <a href="{{ route('admin.logs.verifications') }}" class="small">Lihat semua</a>
                </div>
                <div class="card-body py-2">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>RS</th>
                                    <th>Nomor PIC</th>
                                    <th>NRP</th>
                                    <th>Hasil</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentVerifications as $log)
                                    <tr>
                                        <td class="text-muted small text-nowrap">{{ $log->created_at->format('d/m H:i') }}</td>
                                        <td>{{ $log->hospital_name ?? $log->hospital?->name ?? '-' }}</td>
                                        <td class="small text-nowrap">
                                            @if ($log->pic_phone)
                                                {{ $log->pic_phone }}
                                                @if ($log->pic_label)
                                                    <span class="text-muted">&middot; {{ $log->pic_label }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $log->nrp }}</td>
                                        <td>
                                            @include('admin.logs._result-badge', ['result' => $log->result])
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">Belum ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card hv-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Login RS Terbaru</span>
                    <a href="{{ route('admin.logs.logins') }}" class="small">Lihat semua</a>
                </div>
                <div class="card-body py-2">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>RS</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentLogins as $log)
                                    <tr>
                                        <td class="text-muted small text-nowrap">{{ $log->created_at->format('d/m H:i') }}</td>
                                        <td>{{ $log->hospital_name ?? $log->hospital?->name ?? '-' }}</td>
                                        <td>
                                            @if ($log->status === 'success')
                                                <span class="badge bg-success">Berhasil</span>
                                            @else
                                                <span class="badge bg-danger">Gagal</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">Belum ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if (count($chartDatasets))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const el = document.getElementById('verificationChart');
                if (!el || typeof Chart === 'undefined') return;

                new Chart(el, {
                    type: 'bar',
                    data: {
                        labels: @json($chartLabels),
                        datasets: @json($chartDatasets),
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            x: { stacked: false, grid: { display: false } },
                            y: {
                                stacked: false,
                                beginAtZero: true,
                                ticks: { precision: 0 },
                            },
                        },
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } },
                            tooltip: { mode: 'index', intersect: false },
                        },
                    },
                });
            });
        </script>
    @endif
@endpush
