@extends('layouts.app')

@section('title', 'Tambah RS')

@section('content')
    <h4 class="mb-3">Tambah Rumah Sakit</h4>

    <div class="card hv-card">
        <div class="card-body">
            <form action="{{ route('admin.hospitals.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('admin.hospitals._form', ['hospital' => null])
            </form>
        </div>
    </div>
@endsection
