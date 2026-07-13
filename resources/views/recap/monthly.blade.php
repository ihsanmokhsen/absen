@extends('layouts.app')

@section('title', 'Rekap Bulanan')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4 no-print">
    <div>
        <h1 class="h3 page-title mb-1">Rekap Bulanan</h1>
        <div class="text-secondary">Status pegawai hanya dihitung dari tanggal yang lengkap disubmit oleh 5 bidang.</div>
    </div>
    <form class="d-flex flex-column flex-sm-row gap-2" method="GET" action="{{ route('monthly-recap.index') }}">
        <input class="form-control" type="month" name="month" value="{{ $month }}" aria-label="Bulan rekap">
        <button class="btn btn-primary" type="submit">Terapkan</button>
        <a class="btn btn-outline-success" href="{{ route('monthly-recap.index', ['month' => $month, 'export' => 'csv']) }}">Export CSV</a>
        <button class="btn btn-outline-primary" type="button" onclick="window.print()">Simpan PDF / Cetak</button>
    </form>
</div>

@if ($summary['submitted_fields'] === 0)
    <div class="alert alert-warning no-print">
        Belum ada tanggal dengan submit lengkap 5 bidang pada bulan ini. Laporan masih kosong.
    </div>
@endif

<div class="print-sheet monthly-print-sheet">
    <header class="report-header text-center mb-4">
        <div class="fw-bold text-uppercase">Pemerintah Provinsi Nusa Tenggara Timur</div>
        <div class="fw-bold text-uppercase fs-5">Badan Pendapatan dan Aset Daerah</div>
        <div class="fw-bold text-uppercase mt-2">Rekapitulasi Bulanan Absensi Apel Pagi</div>
    </header>

    <table class="table table-bordered report-meta mb-4">
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

    <div class="print-only mb-3">
        <table class="table table-bordered text-center report-table mb-0">
            <thead>
                <tr>
                    <th>Pegawai Aktif</th>
                    <th>Tanggal Submit</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $summary['employees'] }}</td>
                    <td>{{ $summary['submitted_days'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="row g-3 mb-4 no-print">
        <div class="col-md-6">
            <div class="border rounded p-3 bg-light">
                <div class="stat-label">Pegawai Aktif</div>
                <div class="stat-value">{{ $summary['employees'] }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="border rounded p-3 bg-light">
                <div class="stat-label">Tanggal Submit</div>
                <div class="stat-value">{{ $summary['submitted_days'] }}</div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <div class="fw-bold mb-2">Jumlah Hari Submit per Bidang</div>
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle report-table mb-0">
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
        <table class="table table-bordered table-sm align-middle report-table monthly-report-table">
            <colgroup>
                <col class="monthly-col-number">
                <col class="monthly-col-name">
                <col class="monthly-col-field">
                <col class="monthly-col-days">
                @foreach ($statusOptions as $status)
                    <col class="monthly-col-status">
                @endforeach
            </colgroup>
            <thead class="text-center">
                <tr>
                    <th style="width: 48px;">No</th>
                    <th>Nama Pegawai</th>
                    <th>Bidang</th>
                    <th>Hari Submit</th>
                    <th>Hadir</th>
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
                        <td class="text-center">{{ $row['counts']['HADIR'] ?? 0 }}</td>
                        @foreach ($absenceStatuses as $status)
                            <td class="text-center">{{ $row['counts'][$status] ?? 0 }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="text-center text-secondary py-4" colspan="{{ 5 + count($absenceStatuses) }}">Tidak ada data pegawai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="row mt-5 report-signature">
        <div class="col-6"></div>
        <div class="col-6 text-center">
            <div>Kupang, {{ now(config('app.timezone'))->translatedFormat('d F Y') }}</div>
            <div class="mt-5">Admin Absensi</div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        @page { size: A4 landscape; margin: 8mm; }
        .monthly-print-sheet { font-size: 7pt; }
        .monthly-print-sheet .report-header { font-size: 9pt; }
        .monthly-print-sheet .report-header .fs-5 { font-size: 12pt !important; }
        .monthly-print-sheet .table { margin-bottom: 0.65rem !important; }
        .monthly-report-table { table-layout: fixed; width: 100%; }
        .monthly-report-table th,
        .monthly-report-table td {
            overflow-wrap: anywhere;
            padding: 0.13rem 0.16rem;
            font-size: 6.5pt;
            line-height: 1.15;
        }
        .monthly-report-table .monthly-col-number { width: 3.5%; }
        .monthly-report-table .monthly-col-name { width: 24%; }
        .monthly-report-table .monthly-col-field { width: 11.5%; }
        .monthly-report-table .monthly-col-days { width: 7%; }
        .monthly-report-table .monthly-col-status { width: 7.714%; }
        .monthly-report-table th:nth-child(n+4),
        .monthly-report-table td:nth-child(n+4) { text-align: center; }
    }

    @media screen and (min-width: 992px) {
        .monthly-report-table {
            font-size: 0.78rem;
        }

        .monthly-report-table th,
        .monthly-report-table td {
            padding: 0.25rem 0.3rem;
        }
    }
</style>
@endpush
