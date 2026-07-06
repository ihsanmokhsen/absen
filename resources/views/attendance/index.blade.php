@extends('layouts.app')

@section('title', 'Input Absensi')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 page-title mb-1">Input Absensi</h1>
        <div class="text-secondary">{{ $bidang }} - {{ $formattedDate }}</div>
    </div>
    <form class="row g-2 no-print" method="GET" action="{{ route('attendance.index') }}">
        <div class="col-auto">
            <input class="form-control" type="date" name="date" value="{{ $date }}">
        </div>
        @if ($isAdminView)
            <div class="col-auto">
                <select class="form-select" name="bidang">
                    @foreach ($bidangOptions as $option)
                        <option value="{{ $option }}" @selected($option === $bidang)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <input type="hidden" name="bidang" value="{{ $bidang }}">
        @endif
        <div class="col-auto">
            <button class="btn btn-primary" type="submit">Tampilkan</button>
        </div>
    </form>
</div>

<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3 no-print">
    <div class="text-secondary">
        Cari nomor urut atau nama di dashboard kalau ingin cepat. Di halaman ini, nomor urut sudah ditampilkan di depan nama.
    </div>
</div>

@if ($submission)
    <div class="alert alert-info no-print">
        Bidang ini sudah submit pada {{ $submission->submitted_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}.
        {{ $isAdminView ? 'Admin dapat menyimpan ulang untuk koreksi.' : 'Koreksi setelah submit hanya dapat dilakukan oleh admin BPAD.' }}
    </div>
@endif

<div class="stat-card p-3">
    @if ($employees->isEmpty())
        <div class="text-center text-secondary py-5">Tidak ada pegawai aktif pada bidang ini.</div>
    @else
        @php($formLocked = $submission && ! $isAdminView)
        <form method="POST" action="{{ route('attendance.store') }}" id="attendanceForm">
            @csrf
            <input type="hidden" name="attendance_date" value="{{ $date }}">
            <input type="hidden" name="bidang" value="{{ $bidang }}">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 no-print">
                <div class="d-flex flex-wrap gap-2" id="attendanceSummary" aria-live="polite"></div>
                @unless ($formLocked)
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-sm btn-outline-success" type="button" id="setAllPresentBtn">Set Semua Hadir</button>
                        <button class="btn btn-sm btn-outline-primary" type="button" id="resetSavedBtn">Reset Data</button>
                    </div>
                @endunless
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 48px;">No</th>
                            <th>Nama Pegawai</th>
                            <th style="min-width: 420px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            @php($record = $records->get($employee->id))
                            @php($selectedStatus = old('status.'.$employee->id, $record?->status ?? 'HADIR'))
                            <tr class="attendance-status-row" data-selected-status="{{ $selectedStatus }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-semibold">
                                        <span class="badge text-bg-light border text-dark me-2">No. {{ $employee->attendanceNumber() ?? str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                        {{ $employee->displayName() }}
                                    </div>
                                    <div class="selected-status-line" data-current-status>Status: {{ $statusOptions[$selectedStatus] ?? 'Hadir' }}</div>
                                </td>
                                <td>
                                    <div class="status-button-group" data-original-status="{{ $record?->status ?? 'HADIR' }}">
                                        @foreach ($statusOptions as $value => $label)
                                            @php($statusId = 'attendance-'.$employee->id.'-'.$value)
                                            @php($statusClass = 'status-'.\Illuminate\Support\Str::of($value)->lower()->replace('_', '-'))
                                            <input class="btn-check" type="radio" name="status[{{ $employee->id }}]" id="{{ $statusId }}" value="{{ $value }}" data-original-status="{{ $record?->status ?? 'HADIR' }}" @checked($selectedStatus === $value) @disabled($formLocked) required>
                                            <label class="status-choice {{ $statusClass }}" for="{{ $statusId }}">{{ $label }}</label>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="sticky-submit-bar d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 no-print">
                <div class="d-flex flex-wrap gap-2" id="attendanceSummarySticky" aria-live="polite"></div>
                <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('submissions.index') }}">Status Submit</a>
                @if ($submission && ! $isAdminView)
                    <button class="btn btn-primary" type="button" disabled>Sudah Submit</button>
                @else
                    <button class="btn btn-primary" type="submit">{{ $submission ? 'Update Absensi Bidang' : 'Submit Bidang' }}</button>
                @endif
                </div>
            </div>
        </form>

        <div class="modal fade" id="submitConfirmModal" tabindex="-1" aria-labelledby="submitConfirmTitle" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="submitConfirmTitle">Konfirmasi Submit</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="fw-semibold mb-2">{{ $bidang }} - {{ $formattedDate }}</div>
                        <div id="confirmSummary"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="confirmSubmitBtn">Ya, Submit</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const form = document.getElementById('attendanceForm');

        if (!form) {
            return;
        }

        const statusLabels = @json($statusOptions);
        const statusOrder = Object.keys(statusLabels);
        const radios = Array.from(form.querySelectorAll('input[type="radio"][name^="status["]'));
        const summary = document.getElementById('attendanceSummary');
        const stickySummary = document.getElementById('attendanceSummarySticky');
        const confirmSummary = document.getElementById('confirmSummary');
        const confirmButton = document.getElementById('confirmSubmitBtn');
        const modalElement = document.getElementById('submitConfirmModal');
        const confirmModal = modalElement ? new bootstrap.Modal(modalElement) : null;

        const groups = () => Array.from(new Set(radios.map((radio) => radio.name)));
        const checkedRadios = () => radios.filter((radio) => radio.checked);

        const totals = () => {
            const counts = Object.fromEntries(statusOrder.map((status) => [status, 0]));

            checkedRadios().forEach((radio) => {
                counts[radio.value] = (counts[radio.value] || 0) + 1;
            });

            return counts;
        };

        const syncRows = () => {
            checkedRadios().forEach((radio) => {
                const row = radio.closest('.attendance-status-row');
                const selectedStatus = row?.querySelector('[data-current-status]');

                if (!row) {
                    return;
                }

                row.dataset.selectedStatus = radio.value;

                if (selectedStatus) {
                    selectedStatus.textContent = `Status: ${statusLabels[radio.value] || radio.value}`;
                }
            });
        };

        const renderSummary = () => {
            const counts = totals();
            const html = statusOrder
                .filter((status) => status === 'HADIR' || counts[status] > 0)
                .map((status) => `<span class="summary-pill">${statusLabels[status]}: <strong>${counts[status] || 0}</strong></span>`)
                .join('');

            if (summary) {
                summary.innerHTML = html;
            }

            if (stickySummary) {
                stickySummary.innerHTML = html;
            }
        };

        const refresh = () => {
            syncRows();
            renderSummary();
        };

        radios.forEach((radio) => {
            radio.addEventListener('change', refresh);
        });

        document.getElementById('setAllPresentBtn')?.addEventListener('click', () => {
            groups().forEach((name) => {
                const present = radios.find((radio) => radio.name === name && radio.value === 'HADIR');

                if (present) {
                    present.checked = true;
                }
            });
            refresh();
        });

        document.getElementById('resetSavedBtn')?.addEventListener('click', () => {
            groups().forEach((name) => {
                const groupRadios = radios.filter((radio) => radio.name === name);
                const original = groupRadios[0]?.dataset.originalStatus || 'HADIR';
                const originalRadio = groupRadios.find((radio) => radio.value === original);

                if (originalRadio) {
                    originalRadio.checked = true;
                }
            });
            refresh();
        });

        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true' || !confirmModal) {
                return;
            }

            event.preventDefault();

            const counts = totals();
            const rows = [
                ['Jumlah', groups().length],
                ['Hadir', counts.HADIR || 0],
                ['Kurang', groups().length - (counts.HADIR || 0)],
                ...statusOrder
                    .filter((status) => status !== 'HADIR' && counts[status] > 0)
                    .map((status) => [statusLabels[status], counts[status]]),
            ];

            confirmSummary.innerHTML = `<table class="table table-sm table-bordered mb-0"><tbody>${rows
                .map(([label, value]) => `<tr><th>${label}</th><td class="text-end">${value}</td></tr>`)
                .join('')}</tbody></table>`;
            confirmModal.show();
        });

        confirmButton?.addEventListener('click', () => {
            form.dataset.confirmed = 'true';
            confirmModal?.hide();
            form.requestSubmit();
        });

        refresh();
    })();
</script>
@endpush
