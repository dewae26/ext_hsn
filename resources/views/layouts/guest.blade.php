<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HasnurVerif') - HasnurVerif</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-hv min-vh-100 d-flex flex-column">
    <main class="flex-grow-1 d-flex align-items-center justify-content-center py-4 hv-container">
        <div class="w-100" style="max-width: 460px;">
            <div class="text-center text-white mb-4">
                <img src="{{ asset('img/logo_hasnur_group.png') }}" alt="Hasnur Group"
                    class="bg-white rounded-3 p-2" style="height: 72px;">
                <h2 class="mt-3 mb-0 fw-bold">HasnurVerif</h2>
                <div class="small text-white-50">Verifikasi Status Karyawan Hasnur Group</div>
            </div>
            <div class="card hv-card p-4">
                <x-flash />
                @yield('content')
            </div>
            <div class="text-center text-white-50 small mt-3">
                &copy; {{ date('Y') }} Hasnur Group
            </div>
        </div>
    </main>
</body>
</html>
