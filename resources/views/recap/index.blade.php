@extends('layouts.app')

@section('title', 'Rekap Harian')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4 no-print">
    <div>
        <h1 class="h3 page-title mb-1">Rekap Harian</h1>
        <div class="text-secondary">{{ $formattedDate }}</div>
    </div>
    <form class="d-flex gap-2" method="GET" action="{{ route('recap.index') }}">
        <input class="form-control" type="date" name="date" value="{{ $date }}">
        <button class="btn btn-primary" type="submit">Terapkan</button>
        <button class="btn btn-outline-primary" type="button" onclick="window.print()">Export PDF / Print</button>
    </form>
</div>

@unless ($allSubmitted)
    <div class="alert alert-warning no-print">
        Belum semua bidang submit untuk tanggal ini. Rekap dapat dilihat sementara, tetapi rekap akhir sebaiknya dicetak setelah 5 bidang lengkap.
    </div>
@endunless

<div class="print-sheet">
    <div class="text-center mb-4">
        <div class="fw-bold text-uppercase">Pemerintah Provinsi Nusa Tenggara Timur</div>
        <div class="fw-bold text-uppercase">Badan Pendapatan dan Aset Daerah</div>
        <div class="fw-bold text-uppercase mt-2">Rekapitulasi Absensi Apel Pagi</div>
    </div>

    <table class="table table-bordered mb-4">
        <tbody>
            <tr>
                <th style="width: 240px;">Apel Pagi Hari/Tanggal</th>
                <td>{{ $formattedDate }}</td>
            </tr>
        </tbody>
    </table>

    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead>
                <tr>
                    <th>Jumlah</th>
                    <th>Hadir</th>
                    <th>Kurang</th>
                    @foreach ($absenceStatuses as $status)
                        <th>{{ $statusOptions[$status] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $totalActive }}</td>
                    <td>{{ $hadir }}</td>
                    <td>{{ $kurang }}</td>
                    @foreach ($absenceStatuses as $status)
                        <td>{{ $counts[$status] ?? 0 }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>

    <table class="table table-bordered mb-4">
        <tbody>
            <tr>
                <th style="width: 160px;">Keterangan</th>
                <td>{{ $details }}</td>
            </tr>
        </tbody>
    </table>

    @if ($absenceRecords->isNotEmpty())
        <div class="mb-4">
            <div class="fw-bold mb-2">Daftar Nama Keterangan</div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 160px;">Status</th>
                            <th>Nama Pegawai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($absenceStatuses as $status)
                            @php($recordsByStatus = $absenceRecords->get($status, collect()))
                            @if ($recordsByStatus->isNotEmpty())
                                <tr>
                                    <td class="fw-semibold">{{ $statusOptions[$status] }}</td>
                                    <td>
                                        <ol class="mb-0 ps-3">
                                            @foreach ($recordsByStatus as $record)
                                                <li>
                                                    <span class="badge text-bg-light border text-dark me-1">No. {{ $record->employee->attendanceNumber() ?? '-' }}</span>
                                                    {{ $record->employee->displayName() }}
                                                    <span class="text-secondary">({{ $record->employee->bidang }})</span>
                                                    @if ($record->note)
                                                        - {{ $record->note }}
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ol>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="row mt-5">
        <div class="col-6"></div>
        <div class="col-6 text-center">
            <div>Kupang, {{ now(config('app.timezone'))->translatedFormat('d F Y') }}</div>
            <div class="mt-5">Admin Absensi</div>
        </div>
    </div>
</div>
@endsection
