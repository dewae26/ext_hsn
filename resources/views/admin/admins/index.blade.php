@extends('layouts.app')

@section('title', 'Kelola Admin')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h4 class="mb-0 fw-bold">Kelola Admin</h4>
        <button class="btn btn-hv" data-bs-toggle="collapse" data-bs-target="#formTambahAdmin">
            <i class="bi bi-plus-lg me-1"></i> Tambah Admin
        </button>
    </div>

    <div class="collapse {{ $errors->any() && old('_form') === 'create' ? 'show' : '' }}" id="formTambahAdmin">
        <div class="card hv-card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.admins.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_form" value="create">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">NRP <span class="text-danger">*</span></label>
                            <input type="text" name="employee_id" value="{{ old('employee_id') }}" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_super_admin" value="1" id="newSuper">
                                <label class="form-check-label" for="newSuper">Super Admin</label>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-hv mt-3" type="submit">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card hv-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>NRP</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Super Admin</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($admins as $admin)
                            <tr>
                                <td>{{ $admin->employee_id }}</td>
                                <td>{{ $admin->name }}</td>
                                <td>{{ $admin->email ?? '-' }}</td>
                                <td>
                                    @if ($admin->is_super_admin)
                                        <span class="badge bg-primary">Ya</span>
                                    @else
                                        <span class="text-muted small">Tidak</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($admin->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#editAdmin{{ $admin->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if (auth()->id() !== $admin->id)
                                        <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Hapus admin {{ $admin->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada admin.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $admins->links() }}
        </div>
    </div>

    {{-- Modal diletakkan di luar tabel agar render benar --}}
    @foreach ($admins as $admin)
        <div class="modal fade" id="editAdmin{{ $admin->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('admin.admins.update', $admin) }}" method="POST" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Edit Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">NRP</label>
                            <input type="text" class="form-control" value="{{ $admin->employee_id }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" value="{{ $admin->name }}" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ $admin->email }}" class="form-control">
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_super_admin" value="1"
                                id="super{{ $admin->id }}" @checked($admin->is_super_admin)>
                            <label class="form-check-label" for="super{{ $admin->id }}">Super Admin</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                id="active{{ $admin->id }}" @checked($admin->is_active)>
                            <label class="form-check-label" for="active{{ $admin->id }}">Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-hv">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection
