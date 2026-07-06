@extends('layouts.app')

@section('title', 'Status Submit')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 page-title mb-1">{{ $isAdminView ? 'Status Submit 5 Bidang' : 'Status Submit Bidang' }}</h1>
        <div class="text-secondary">{{ $formattedDate }}</div>
    </div>
    <form class="d-flex gap-2 no-print" method="GET" action="{{ route('submissions.index') }}">
        <input class="form-control" type="date" name="date" value="{{ $date }}">
        <button class="btn btn-primary" type="submit">Terapkan</button>
    </form>
</div>

<div class="stat-card p-3">
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <th>Bidang</th>
                    <th>Status</th>
                    <th>Disubmit Oleh</th>
                    <th>Waktu Submit</th>
                    <th class="no-print">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bidangOptions as $option)
                    @php($submission = $submissions->get($option))
                    <tr>
                        <td class="fw-semibold">{{ $option }}</td>
                        <td>
                            @if ($submission)
                                <span class="badge text-bg-success">Sudah Submit</span>
                            @else
                                <span class="badge text-bg-secondary">Belum Submit</span>
                            @endif
                        </td>
                        <td>{{ $submission?->submittedBy?->name ?? '-' }}</td>
                        <td>{{ $submission?->submitted_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="no-print">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('attendance.index', ['date' => $date, 'bidang' => $option]) }}">
                                {{ $submission ? 'Edit' : 'Input' }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($isAdminView)
        <div class="d-flex justify-content-end mt-3 no-print">
            <a class="btn btn-primary" href="{{ route('recap.index', ['date' => $date]) }}">Buka Rekap Harian</a>
        </div>
    @endif
</div>
@endsection
