@extends('layouts.app')

@section('title', 'Log Login RS')

@section('content')
    <h4 class="mb-3 fw-bold">Log Login Rumah Sakit</h4>

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
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="success" @selected($filters['status'] === 'success')>Berhasil</option>
                        <option value="failed" @selected($filters['status'] === 'failed')>Gagal</option>
                    </select>
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
                <table id="loginsTable" class="table table-sm table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>RS</th>
                            <th>Nomor PIC</th>
                            <th>Keterangan PIC</th>
                            <th>Status</th>
                            <th>Alasan</th>
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
                                    @if ($log->phone)
                                        <i class="bi bi-whatsapp text-success me-1"></i>{{ $log->phone }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small">{{ $log->pic_label ?? '-' }}</td>
                                <td>
                                    @if ($log->status === 'success')
                                        <span class="badge bg-success">Berhasil</span>
                                    @else
                                        <span class="badge bg-danger">Gagal</span>
                                    @endif
                                </td>
                                <td class="small">{{ $log->reason ?? '-' }}</td>
                                <td class="text-muted small">{{ $log->ip }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada data.</td>
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
            new DataTable('#loginsTable', {
                pageLength: 25,
                lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'Semua']],
                order: [[0, 'desc']],
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
