<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    {{-- token CSRF untuk request AJAX POST/DELETE (misal pengaturan keyword) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Otomatisasi - Simfoni</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <link id="pagestyle" href="{{ asset('assets/css/argon-dashboard.css') }}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    {{-- ?v=... berubah tiap file CSS diubah --}}
    <link href="{{ asset('assets/css/otomatisasi.css') }}?v={{ filemtime(public_path('assets/css/otomatisasi.css')) }}" rel="stylesheet" />
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg otomatisasi-navbar border-radius-lg top-0 z-index-3 shadow position-relative mt-3 py-2 mx-3">
        <div class="container-fluid">
            <span class="navbar-brand font-weight-bolder ms-3">Otomatisasi SIMFONI</span>
            @auth
            <form method="POST" action="{{ route('otomatisasi.logout') }}" class="ms-auto">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary me-3">Logout</button>
            </form>
            @endauth
        </div>
    </nav>

    <main class="container-fluid mt-4 px-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>
    @yield('scripts')
</body>
</html>