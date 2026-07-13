@extends('layouts.app')

@section('title', 'Rekap Bulanan')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4 no-print">
    <div>
        <h1 class="h3 page-title mb-1">Rekap Bulanan</h1>
        <div class="text-secondary">Total kehadiran pegawai berdasarkan data bidang yang sudah submit.</div>
    </div>
    <form class="d-flex flex-column flex-sm-row gap-2" method="GET" action="{{ route('monthly-recap.index') }}">
        <input class="form-control" type="month" name="month" value="{{ $month }}" aria-label="Bulan rekap">
        <button class="btn btn-primary" type="submit">Terapkan</button>
        <a class="btn btn-outline-success" href="{{ route('monthly-recap.index', ['month' => $month, 'export' => 'csv']) }}">Export CSV</a>
        <button class="btn btn-outline-primary" type="button" onclick="window.print()">Print / PDF</button>
    </form>
</div>

@if ($summary['submitted_fields'] === 0)
    <div class="alert alert-warning no-print">
        Belum ada bidang yang submit pada bulan ini. Laporan masih kosong.
    </div>
@endif

<div class="print-sheet">
    <div class="text-center mb-4">
        <div class="fw-bold text-uppercase">Pemerintah Provinsi Nusa Tenggara Timur</div>
        <div class="fw-bold text-uppercase">Badan Pendapatan dan Aset Daerah</div>
        <div class="fw-bold text-uppercase mt-2">Rekapitulasi Bulanan Absensi Apel Pagi</div>
    </div>

    <table class="table table-bordered mb-4">
        <tbody>
            <tr>
                <th style="width: 220px;">Bulan</th>
                <td>{{ $monthLabel }}</td>
            </tr>
            <tr>
                <th>Dicetak</th>
                <td>{{ $generatedAt }} WITA</td>
            </tr>
        </tbody>
    </table>

    <div class="row g-3 mb-4 no-print">
        <div class="col-md-3">
            <div class="border rounded p-3 bg-light">
                <div class="stat-label">Pegawai Aktif</div>
                <div class="stat-value">{{ $summary['employees'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="border rounded p-3 bg-light">
                <div class="stat-label">Tanggal Submit</div>
                <div class="stat-value">{{ $summary['submitted_days'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="border rounded p-3 bg-light">
                <div class="stat-label">Total Hadir</div>
                <div class="stat-value">{{ $summary['hadir'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="border rounded p-3 bg-light">
                <div class="stat-label">Total Kurang</div>
                <div class="stat-value">{{ $summary['kurang'] }}</div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <div class="fw-bold mb-2">Jumlah Hari Submit per Bidang</div>
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle mb-0">
                <thead>
                    <tr>
                        @foreach ($bidangOptions as $bidang)
                            <th>{{ $bidang }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach ($bidangOptions as $bidang)
                            <td>{{ $submittedDaysByBidang->get($bidang, 0) }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
            <thead class="text-center">
                <tr>
                    <th style="width: 48px;">No</th>
                    <th>Nama Pegawai</th>
                    <th>Bidang</th>
                    <th>Hari Submit</th>
                    <th>Hadir</th>
                    <th>Kurang</th>
                    @foreach ($absenceStatuses as $status)
                        <th>{{ $statusOptions[$status] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <span class="fw-semibold">{{ $row['employee']->displayName() }}</span>
                        </td>
                        <td>{{ $row['employee']->bidang }}</td>
                        <td class="text-center">{{ $row['submitted_days'] }}</td>
                        <td class="text-center">{{ $row['hadir'] }}</td>
                        <td class="text-center">{{ $row['kurang'] }}</td>
                        @foreach ($absenceStatuses as $status)
                            <td class="text-center">{{ $row['counts'][$status] ?? 0 }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="text-center text-secondary py-4" colspan="{{ 6 + count($absenceStatuses) }}">Tidak ada data pegawai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="row mt-5">
        <div class="col-6"></div>
        <div class="col-6 text-center">
            <div>Kupang, {{ now(config('app.timezone'))->translatedFormat('d F Y') }}</div>
            <div class="mt-5">Admin Absensi</div>
        </div>
    </div>
</div>
@endsection
