@php
    $hospital = $hospital ?? null;
    $rows = old('contacts');
    if ($rows === null) {
        $rows = $hospital && $hospital->contacts->isNotEmpty()
            ? $hospital->contacts->map(fn ($c) => ['label' => $c->label, 'phone' => $c->phone])->all()
            : [['label' => '', 'phone' => '']];
    }
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nama Rumah Sakit <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $hospital?->name) }}"
            class="form-control @error('name') is-invalid @enderror" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Link Route (slug) <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">{{ url('/rs') }}/</span>
            <input type="text" name="slug" value="{{ old('slug', $hospital?->slug) }}"
                class="form-control @error('slug') is-invalid @enderror"
                placeholder="contoh: rsu-banjarmasin">
            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="form-text">Biarkan kosong untuk dibuat otomatis dari nama RS.</div>
    </div>

    <div class="col-12">
        <label class="form-label">Nomor WhatsApp PIC RS <span class="text-danger">*</span></label>
        <div id="contactRows">
            @foreach ($rows as $i => $row)
                <div class="contact-row border rounded-3 p-3 mb-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-semibold text-hv">
                            <i class="bi bi-whatsapp me-1"></i> Nomor PIC
                            <span class="contact-index">#{{ $i + 1 }}</span>
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-contact">
                            <i class="bi bi-trash me-1"></i> Hapus
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small mb-1">Nomor WhatsApp</label>
                            <input type="text" name="contacts[{{ $i }}][phone]" value="{{ $row['phone'] ?? '' }}"
                                class="form-control @error('contacts.'.$i.'.phone') is-invalid @enderror"
                                placeholder="08xxxxxxxxxx" inputmode="tel" required>
                            @error('contacts.'.$i.'.phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1">Detail / Keterangan</label>
                            <input type="text" name="contacts[{{ $i }}][label]" value="{{ $row['label'] ?? '' }}"
                                class="form-control" placeholder="mis. RS Mitra Keluarga Pamulang">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @error('contacts') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
        <button type="button" class="btn btn-sm btn-outline-primary" id="addContact">
            <i class="bi bi-plus-lg me-1"></i> Tambah Nomor
        </button>
        <div class="form-text">Satu link RS dapat dipakai login oleh semua nomor yang terdaftar di sini.</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Upload PKS (PDF, opsional)</label>
        <input type="file" name="pks" accept="application/pdf"
            class="form-control @error('pks') is-invalid @enderror">
        @error('pks') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if ($hospital?->hasPks())
            <div class="form-text">
                PKS saat ini:
                <a href="{{ route('admin.hospitals.pks', $hospital) }}" target="_blank">Lihat</a>
                &middot;
                <a href="{{ route('admin.hospitals.pks.download', $hospital) }}">Unduh</a>.
                Unggah file baru untuk mengganti.
            </div>
            <button type="submit" form="deletePksForm" class="btn btn-sm btn-outline-danger mt-2"
                onclick="return confirm('Hapus PKS RS ini? Tindakan ini tidak dapat dibatalkan.');">
                <i class="bi bi-trash me-1"></i> Hapus PKS
            </button>
        @else
            <div class="form-text">Maksimal {{ config('hasnurverif.pks.max_size_kb') / 1024 }} MB.</div>
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label">Detail / Keterangan RS</label>
        <textarea name="detail" rows="3" class="form-control @error('detail') is-invalid @enderror"
            placeholder="Catatan umum tentang RS ini">{{ old('detail', $hospital?->detail) }}</textarea>
        @error('detail') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1"
                {{ old('is_active', $hospital?->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Status Aktif</label>
        </div>
        <div class="form-text">RS nonaktif tidak dapat mengakses link-nya.</div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-hv">
        <i class="bi bi-save me-1"></i> Simpan
    </button>
    <a href="{{ route('admin.hospitals.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('contactRows');
            const addBtn = document.getElementById('addContact');
            if (!container || !addBtn) return;

            function reindex() {
                const rows = container.querySelectorAll('.contact-row');

                rows.forEach(function (row, i) {
                    row.querySelectorAll('input').forEach(function (input) {
                        input.name = input.name.replace(/contacts\[\d+\]/, 'contacts[' + i + ']');
                    });
                    const index = row.querySelector('.contact-index');
                    if (index) index.textContent = '#' + (i + 1);
                });

                container.querySelectorAll('.remove-contact').forEach(function (btn) {
                    btn.disabled = rows.length <= 1;
                });
            }

            addBtn.addEventListener('click', function () {
                const template = container.querySelector('.contact-row');
                const row = template.cloneNode(true);
                row.querySelectorAll('input').forEach(function (input) { input.value = ''; });
                row.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
                row.querySelectorAll('.invalid-feedback').forEach(function (el) { el.remove(); });
                container.appendChild(row);
                reindex();
            });

            container.addEventListener('click', function (event) {
                const btn = event.target.closest('.remove-contact');
                if (!btn) return;
                if (container.querySelectorAll('.contact-row').length <= 1) return;
                btn.closest('.contact-row').remove();
                reindex();
            });

            reindex();
        });
    </script>
@endpush
