@extends('layouts.app')

@section('title', 'Log Verifikasi')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h4 class="mb-0 fw-bold">Log Verifikasi NRP</h4>
        <a href="{{ route('admin.logs.export', request()->query()) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </a>
    </div>

    <div class="card hv-card">
        <div class="card-body">
            <form class="row g-2 mb-3" method="GET">
                <div class="col-sm-6 col-md-3">
                    <select name="hospital_id" class="form-select">
                        <option value="">Semua RS</option>
                        @foreach ($hospitals as $hospital)
                            <option value="{{ $hospital->id }}" @selected($filters['hospital_id'] == $hospital->id)>
                                {{ $hospital->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-md-2">
                    <select name="result" class="form-select">
                        <option value="">Semua Hasil</option>
                        <option value="active" @selected($filters['result'] === 'active')>Aktif</option>
                        <option value="inactive" @selected($filters['result'] === 'inactive')>Tidak Aktif</option>
                        <option value="not_found" @selected($filters['result'] === 'not_found')>Tidak Ditemukan</option>
                    </select>
                </div>
                <div class="col-sm-6 col-md-2">
                    <input type="text" name="nrp" value="{{ $filters['nrp'] }}" class="form-control" placeholder="NRP">
                </div>
                <div class="col-sm-6 col-md-2">
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
                </div>
                <div class="col-sm-6 col-md-2">
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
                </div>
                <div class="col-auto">
                    <button class="btn btn-hv" type="submit">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table id="verificationsTable" class="table table-sm table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>RS</th>
                            <th>Nomor PIC</th>
                            <th>NRP</th>
                            <th>Nama</th>
                            <th>Entitas</th>
                            <th>PT</th>
                            <th>KTP</th>
                            <th>Nominal Kamar</th>
                            <th>Hasil</th>
                            <th>Feedback</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="text-muted small text-nowrap" data-order="{{ $log->created_at->timestamp }}">
                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td>{{ $log->hospital_name ?? $log->hospital?->name ?? '-' }}</td>
                                <td class="small text-nowrap">
                                    @if ($log->pic_phone)
                                        <i class="bi bi-whatsapp text-success me-1"></i>{{ $log->pic_phone }}
                                        @if ($log->pic_label)
                                            <span class="text-muted">&middot; {{ $log->pic_label }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $log->nrp }}</td>
                                <td>{{ $log->employee_name ?? '-' }}</td>
                                <td>{{ $log->group_company ?? '-' }}</td>
                                <td>{{ $log->company_name ?? '-' }}</td>
                                <td class="small text-nowrap">{{ $log->ktp ?? '-' }}</td>
                                <td class="text-nowrap">
                                    @if ($log->room_rate)
                                        Rp {{ number_format($log->room_rate, 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>@include('admin.logs._result-badge', ['result' => $log->result])</td>
                                <td class="small">{{ $log->feedback }}</td>
                                <td class="text-muted small">{{ $log->ip }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new DataTable('#verificationsTable', {
                pageLength: 25,
                lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'Semua']],
                order: [[0, 'desc']],
                columnDefs: [{ targets: [0, 6], orderable: true }],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(difilter dari _MAX_ total data)',
                    zeroRecords: 'Tidak ada data yang cocok',
                    emptyTable: 'Belum ada data',
                    paginate: { previous: 'Sebelumnya', next: 'Selanjutnya' },
                },
            });
        });
    </script>
@endpush
