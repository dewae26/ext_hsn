@extends('layouts.app')

@section('title', 'Edit RS')

@section('content')
    <h4 class="mb-3">Edit Rumah Sakit</h4>

    <div class="card hv-card">
        <div class="card-body">
            <form action="{{ route('admin.hospitals.update', $hospital) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.hospitals._form')
            </form>

            {{-- Form terpisah (di luar form utama) untuk hapus PKS --}}
            @if ($hospital->hasPks())
                <form id="deletePksForm" action="{{ route('admin.hospitals.pks.destroy', $hospital) }}"
                    method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>
@endsection
