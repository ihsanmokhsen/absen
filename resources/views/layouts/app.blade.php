<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bpad-blue: #0b2f5f;
            --bpad-blue-soft: #123f78;
            --bpad-light: #f4f7fb;
            --bpad-border: #d9e2ef;
        }

        body {
            background: var(--bpad-light);
            color: #182230;
            font-size: 0.95rem;
        }

        .navbar-bpad {
            background: var(--bpad-blue);
        }

        .navbar-bpad .navbar-brand,
        .navbar-bpad .nav-link,
        .navbar-bpad .navbar-text {
            color: #fff;
        }

        .navbar-clock {
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .navbar-bpad .nav-link {
            border-radius: 0.375rem;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .navbar-bpad .nav-link:hover,
        .navbar-bpad .nav-link.active {
            background: rgba(255, 255, 255, 0.14);
            color: #fff;
        }

        .page-title {
            color: var(--bpad-blue);
            font-weight: 700;
            letter-spacing: 0;
        }

        .stat-card {
            border: 1px solid var(--bpad-border);
            border-radius: 0.5rem;
            background: #fff;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .stat-value {
            color: var(--bpad-blue);
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .table thead th {
            background: #eef3f9;
            color: #16345c;
            font-weight: 700;
        }

        .btn-primary {
            background: var(--bpad-blue);
            border-color: var(--bpad-blue);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background: var(--bpad-blue-soft);
            border-color: var(--bpad-blue-soft);
        }

        .print-sheet {
            background: #fff;
            border: 1px solid var(--bpad-border);
            border-radius: 0.5rem;
            padding: 2rem;
        }

        .sticky-submit-bar {
            position: sticky;
            bottom: 0;
            z-index: 10;
            background: rgba(244, 247, 251, 0.96);
            border-top: 1px solid var(--bpad-border);
            backdrop-filter: blur(8px);
            margin: 1rem -1rem -1rem;
            padding: 0.75rem 1rem;
        }

        .summary-pill {
            border: 1px solid var(--bpad-border);
            border-radius: 999px;
            background: #fff;
            padding: 0.35rem 0.7rem;
            font-size: 0.86rem;
            white-space: nowrap;
        }

        .status-button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            justify-content: flex-end;
        }

        .status-choice {
            --status-bg: #eef3f9;
            --status-border: #64748b;
            --status-text: #334155;
            --status-ring: rgba(100, 116, 139, 0.18);
            border: 1px solid #cbd5e1;
            border-radius: 0.375rem;
            background: #fff;
            color: #334155;
            font-weight: 600;
            padding: 0.34rem 0.58rem;
            line-height: 1.15;
            cursor: pointer;
            transition: background-color 0.12s ease, border-color 0.12s ease, box-shadow 0.12s ease, color 0.12s ease;
        }

        .status-choice:hover {
            border-color: var(--status-border);
            color: var(--status-text);
            background: #f8fafc;
        }

        .btn-check:focus + .status-choice {
            outline: 3px solid rgba(11, 47, 95, 0.18);
            outline-offset: 2px;
        }

        .btn-check:checked + .status-choice {
            background: var(--status-bg);
            border-color: var(--status-border);
            color: var(--status-text);
            box-shadow: 0 0 0 0.2rem var(--status-ring);
        }

        .btn-check:checked + .status-choice::after {
            content: "Dipilih";
            display: inline-block;
            margin-left: 0.38rem;
            padding-left: 0.38rem;
            border-left: 1px solid currentColor;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            vertical-align: 0.06rem;
        }

        .status-hadir {
            --status-bg: #eaf7ef;
            --status-border: #218356;
            --status-text: #145536;
            --status-ring: rgba(33, 131, 86, 0.2);
        }

        .status-izin-pagi {
            --status-bg: #eef5ff;
            --status-border: #3b7bc8;
            --status-text: #174a88;
            --status-ring: rgba(59, 123, 200, 0.2);
        }

        .status-sakit {
            --status-bg: #fff8df;
            --status-border: #c48a00;
            --status-text: #704b00;
            --status-ring: rgba(196, 138, 0, 0.22);
        }

        .status-tugas {
            --status-bg: #edf2f7;
            --status-border: #5d6b82;
            --status-text: #2f3b4c;
            --status-ring: rgba(93, 107, 130, 0.2);
        }

        .status-tubel {
            --status-bg: #eef7f6;
            --status-border: #238579;
            --status-text: #105f55;
            --status-ring: rgba(35, 133, 121, 0.2);
        }

        .status-cuti {
            --status-bg: #ecf6ff;
            --status-border: #2b8db8;
            --status-text: #075a7a;
            --status-ring: rgba(43, 141, 184, 0.2);
        }

        .status-terlambat {
            --status-bg: #fff0f1;
            --status-border: #c94955;
            --status-text: #84202a;
            --status-ring: rgba(201, 73, 85, 0.2);
        }

        .attendance-status-row {
            --row-bg: #f8fbff;
            --row-accent: var(--bpad-blue);
            transition: background-color 0.12s ease, border-color 0.12s ease;
        }

        .attendance-status-row[data-selected-status="HADIR"] {
            --row-bg: #f1fbf5;
            --row-accent: #218356;
        }

        .attendance-status-row[data-selected-status="IZIN_PAGI"] {
            --row-bg: #f2f7ff;
            --row-accent: #3b7bc8;
        }

        .attendance-status-row[data-selected-status="SAKIT"] {
            --row-bg: #fff9e8;
            --row-accent: #c48a00;
        }

        .attendance-status-row[data-selected-status="TUGAS"] {
            --row-bg: #f4f7fa;
            --row-accent: #5d6b82;
        }

        .attendance-status-row[data-selected-status="TUBEL"] {
            --row-bg: #f0faf8;
            --row-accent: #238579;
        }

        .attendance-status-row[data-selected-status="CUTI"] {
            --row-bg: #f0f8ff;
            --row-accent: #2b8db8;
        }

        .attendance-status-row[data-selected-status="TERLAMBAT"] {
            --row-bg: #fff4f5;
            --row-accent: #c94955;
        }

        .quick-attendance-row.attendance-status-row {
            background: var(--row-bg);
            border-left: 5px solid var(--row-accent);
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        tr.attendance-status-row > td {
            background: var(--row-bg);
        }

        tr.attendance-status-row > td:first-child {
            border-left: 5px solid var(--row-accent);
        }

        .selected-status-line {
            display: inline-flex;
            align-items: center;
            margin-top: 0.35rem;
            border: 1px solid var(--row-accent);
            border-radius: 999px;
            background: #fff;
            color: #182230;
            padding: 0.16rem 0.55rem;
            font-size: 0.8rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .employee-number-jump {
            border: 1px solid #cbd5e1;
            padding: 0.18rem 0.55rem;
            font-size: 0.82rem;
            line-height: 1.1;
        }

        .employee-number-jump:hover,
        .employee-number-jump:focus {
            filter: brightness(0.97);
        }

        .row-pulse {
            animation: row-pulse 0.9s ease;
        }

        @keyframes row-pulse {
            0% {
                box-shadow: inset 0 0 0 0 rgba(11, 47, 95, 0.0);
            }

            30% {
                box-shadow: inset 0 0 0 9999px rgba(11, 47, 95, 0.05);
            }

            100% {
                box-shadow: inset 0 0 0 0 rgba(11, 47, 95, 0.0);
            }
        }

        .quick-attendance-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 560px);
            gap: 0.75rem;
            align-items: center;
            padding: 0.7rem 0;
            border-bottom: 1px solid var(--bpad-border);
        }

        .quick-attendance-row:last-child {
            border-bottom: 0;
        }

        .quick-bidang-body.is-collapsed {
            display: none;
        }

        @media (max-width: 575.98px) {
            .quick-attendance-row {
                grid-template-columns: 1fr;
            }

            .status-button-group {
                justify-content: flex-start;
            }

            .status-choice {
                flex: 1 1 calc(33.333% - 0.35rem);
                text-align: center;
            }
        }

        @media print {
            @page {
                size: A4;
                margin: 12mm;
            }

            body {
                background: #fff;
                font-size: 12pt;
            }

            .no-print,
            .navbar,
            .alert,
            .btn,
            .sticky-submit-bar {
                display: none !important;
            }

            .container {
                max-width: 100% !important;
                padding: 0 !important;
            }

            .print-sheet {
                border: 0;
                border-radius: 0;
                padding: 0;
            }

            .table th,
            .table td {
                border-color: #222 !important;
            }
        }
    </style>
</head>
<body>
@auth
    <nav class="navbar navbar-expand-lg navbar-bpad no-print">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">BPAD Provinsi NTT</a>
            <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Buka navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}" href="{{ route('attendance.index') }}">Input Absensi</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('submissions.*') ? 'active' : '' }}" href="{{ route('submissions.index') }}">Status Submit</a></li>
                    @if (auth()->user()->isAdmin())
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('recap.*') ? 'active' : '' }}" href="{{ route('recap.index') }}">Rekap Harian</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('monthly-recap.*') ? 'active' : '' }}" href="{{ route('monthly-recap.index') }}">Rekap Bulanan</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}">Pegawai</a></li>
                    @endif
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <a class="btn btn-sm btn-outline-light" href="{{ route('guide') }}">Panduan</a>
                    <span class="navbar-text small navbar-clock text-end">
                        <span class="d-block" data-live-today>{{ now(config('app.timezone'))->locale('id')->translatedFormat('l, d F Y') }}</span>
                        <span class="d-block fw-semibold" data-live-clock>{{ now(config('app.timezone'))->format('H:i:s') }} WITA</span>
                    </span>
                    <span class="navbar-text small">
                        {{ auth()->user()->name }}
                        @unless (auth()->user()->isAdmin())
                            <span class="badge text-bg-light ms-1">{{ auth()->user()->bidang }}</span>
                        @endunless
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-light" type="submit">Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
@endauth

<main class="py-4">
    <div class="container">
        @if (session('success'))
            <div class="alert alert-success no-print">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger no-print">
                <strong>Periksa kembali input.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (() => {
        const todayEl = document.querySelector('[data-live-today]');
        const clockEl = document.querySelector('[data-live-clock]');

        if (!todayEl || !clockEl) {
            return;
        }

        const formatterDate = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Makassar',
            weekday: 'long',
            day: '2-digit',
            month: 'long',
            year: 'numeric',
        });

        const formatterTime = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Makassar',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        });

        const updateClock = () => {
            const now = new Date();
            todayEl.textContent = formatterDate.format(now);
            clockEl.textContent = `${formatterTime.format(now)} WITA`;
        };

        updateClock();
        window.setInterval(updateClock, 1000);
    })();
</script>
@stack('scripts')
</body>
</html>
