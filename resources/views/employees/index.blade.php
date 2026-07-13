@extends('layouts.app')

@section('title', 'Pegawai')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 page-title mb-1">Database Pegawai</h1>
        <div class="text-secondary">Kelola pegawai aktif dan nonaktif per bidang.</div>
    </div>
    <a class="btn btn-primary align-self-start no-print" href="{{ route('employees.create') }}">Tambah Pegawai</a>
</div>

<form class="row g-2 mb-3 no-print" method="GET" action="{{ route('employees.index') }}">
    <div class="col-md-4">
        <select class="form-select" name="bidang">
            <option value="">Semua Bidang</option>
            @foreach ($bidangOptions as $option)
                <option value="{{ $option }}" @selected($selectedBidang === $option)>{{ $option }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" name="status">
            <option value="active" @selected($selectedStatus === 'active')>Aktif</option>
            <option value="inactive" @selected($selectedStatus === 'inactive')>Nonaktif</option>
            <option value="all" @selected($selectedStatus === 'all')>Semua Status</option>
        </select>
    </div>
    <div class="col-md-auto">
        <button class="btn btn-primary" type="submit">Filter</button>
    </div>
</form>

<div class="stat-card p-3">
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Bidang</th>
                    <th>Status</th>
                    <th class="no-print" style="width: 180px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $employee)
                    <tr>
                        <td class="fw-semibold">{{ $employee->displayName() }}</td>
                        <td>{{ $employee->bidang }}</td>
                        <td>
                            @if ($employee->is_active)
                                <span class="badge text-bg-success">Aktif</span>
                            @else
                                <span class="badge text-bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="no-print">
                            <div class="d-flex gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('employees.edit', $employee) }}">Edit</a>
                                @if ($employee->is_active)
                                    <form method="POST" action="{{ route('employees.deactivate', $employee) }}" onsubmit="return confirm('Nonaktifkan pegawai ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Nonaktifkan</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center text-secondary py-4" colspan="4">Tidak ada data pegawai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
