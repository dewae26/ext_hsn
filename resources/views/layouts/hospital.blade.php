<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HasnurVerif') - HasnurVerif</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light min-vh-100 d-flex flex-column">
    <div class="container">
        <div class="navbar hv-topbar mt-3 mb-4">
            <div class="d-flex align-items-center justify-content-between w-100 px-1 gap-2">
                <a class="navbar-brand d-flex align-items-center gap-2 mb-0" href="javascript:void(0)">
                    <img src="{{ asset('img/logo_hasnur_200.png') }}" alt="Hasnur Group" height="38" width="38">
                    <span class="d-flex flex-column lh-1">
                        <span class="fw-bold">HasnurVerif</span>
                        <span class="brand-sub">HASNUR GROUP</span>
                    </span>
                </a>
                <div class="d-flex align-items-center gap-2">
                    @if (isset($hospital) && $hospital?->hasPks())
                        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#rsPksModal">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Lihat PKS
                        </button>
                    @endif
                    @hasSection('header-action')
                        @yield('header-action')
                    @endif
                </div>
            </div>
        </div>
    </div>

    <main class="flex-grow-1 py-2">
        <div class="container">
            <x-flash />
            @yield('content')
        </div>
    </main>

    <footer class="text-center text-muted small py-3">
        &copy; {{ date('Y') }} Hasnur Group &mdash; HasnurVerif
    </footer>

    @if (isset($hospital) && $hospital?->hasPks())
        {{-- View-only: tanpa tombol download --}}
        <div class="modal fade" id="rsPksModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Dokumen PKS &mdash; {{ $hospital->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body p-3" style="max-height:75vh;overflow:auto;background:#f5f7fa;">
                        <div id="rsPksViewer"></div>
                    </div>
                    <div class="modal-footer">
                        <span class="text-muted small me-auto"><i class="bi bi-eye me-1"></i> Hanya untuk dilihat.</span>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const modal = document.getElementById('rsPksModal');
                    const viewer = document.getElementById('rsPksViewer');
                    if (!modal || !viewer) return;

                    const pdfUrl = @json(route('hospital.pks', $hospital->slug).'?v='.optional($hospital->updated_at)->timestamp);

                    modal.addEventListener('show.bs.modal', function () {
                        if (!viewer.dataset.loaded && window.HvPdf) {
                            window.HvPdf.render(viewer, pdfUrl);
                            viewer.dataset.loaded = '1';
                        }
                    });
                    modal.addEventListener('hidden.bs.modal', function () {
                        if (window.HvPdf) window.HvPdf.clear(viewer);
                        delete viewer.dataset.loaded;
                    });
                });
            </script>
        @endpush
    @endif

    @stack('scripts')
</body>
</html>
