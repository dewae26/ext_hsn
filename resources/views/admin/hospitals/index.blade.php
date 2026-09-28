@extends('layouts.app')

@section('title', 'Rumah Sakit')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h4 class="mb-0 fw-bold">Data Rumah Sakit</h4>
        <a href="{{ route('admin.hospitals.create') }}" class="btn btn-hv">
            <i class="bi bi-plus-lg me-1"></i> Tambah RS
        </a>
    </div>

    <div class="card hv-card">
        <div class="card-body">
            <form class="row g-2 mb-3" method="GET">
                <div class="col-sm-8 col-md-5">
                    <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Cari nama atau slug...">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-secondary" type="submit">Cari</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nama RS</th>
                            <th>Link Akses</th>
                            <th>Nomor WA PIC</th>
                            <th>PKS</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($hospitals as $hospital)
                            @php($link = route('hospital.login', $hospital->slug))
                            <tr>
                                <td class="fw-semibold">{{ $hospital->name }}</td>
                                <td>
                                    <div class="input-group input-group-sm" style="max-width: 300px;">
                                        <input type="text" class="form-control" value="{{ $link }}" readonly>
                                        <button class="btn btn-outline-secondary" type="button"
                                            onclick="navigator.clipboard.writeText('{{ $link }}'); this.innerHTML='<i class=&quot;bi bi-check2&quot;></i>';">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    @forelse ($hospital->contacts as $contact)
                                        <div class="small">
                                            <i class="bi bi-whatsapp text-success me-1"></i>
                                            {{ $contact->phone }}
                                            @if ($contact->label)
                                                <span class="text-muted">&middot; {{ $contact->label }}</span>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-muted small">-</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if ($hospital->hasPks())
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#pksModal"
                                            data-pks-view="{{ route('admin.hospitals.pks', $hospital).'?v='.optional($hospital->updated_at)->timestamp }}"
                                            data-pks-download="{{ route('admin.hospitals.pks.download', $hospital) }}"
                                            data-pks-title="{{ $hospital->name }}">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> Lihat PKS
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($hospital->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.hospitals.edit', $hospital) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.hospitals.destroy', $hospital) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Hapus RS {{ $hospital->name }}? Log aktivitas akan tetap tersimpan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data RS.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $hospitals->links() }}
        </div>
    </div>

    <div class="modal fade" id="pksModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Dokumen PKS <span id="pksTitle" class="text-muted"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-3" style="max-height:75vh;overflow:auto;background:#f5f7fa;">
                    <div id="pksViewer"></div>
                </div>
                <div class="modal-footer">
                    <a id="pksDownload" class="btn btn-outline-success me-auto" href="#">
                        <i class="bi bi-download me-1"></i> Download
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('pksModal');
            if (!modal) return;

            const viewer = document.getElementById('pksViewer');
            const title = document.getElementById('pksTitle');
            const download = document.getElementById('pksDownload');

            modal.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                title.textContent = '- ' + btn.getAttribute('data-pks-title');
                download.href = btn.getAttribute('data-pks-download');
                if (window.HvPdf) window.HvPdf.render(viewer, btn.getAttribute('data-pks-view'));
            });

            modal.addEventListener('hidden.bs.modal', function () {
                if (window.HvPdf) window.HvPdf.clear(viewer);
            });
        });
    </script>
@endpush
