@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 page-title mb-1">Dashboard</h1>
        <div class="text-secondary">Tanggal aktif: {{ $formattedDate }}</div>
        <div class="text-secondary">
            Hari ini:
            <span data-dashboard-today>{{ now(config('app.timezone'))->locale('id')->translatedFormat('l, d F Y') }}</span>
            -
            <span data-dashboard-clock>{{ now(config('app.timezone'))->format('H:i:s') }} WITA</span>
        </div>
        @unless ($isAdminView)
            <div class="text-secondary">Bidang: {{ $bidangOptions[0] ?? '-' }}</div>
        @endunless
    </div>
    <form class="d-flex flex-column flex-sm-row gap-2 no-print" method="GET" action="{{ route('dashboard') }}" id="activeDateForm">
        <input class="form-control" type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" aria-label="Tanggal aktif">
        <div class="small text-secondary align-self-sm-center text-nowrap">Otomatis diterapkan</div>
    </form>
</div>

<section class="stat-card p-3 mb-4" id="liveDailyRecap">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-2 mb-3">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h2 class="h5 mb-0">Rekap Harian</h2>
                <span class="badge text-bg-primary">Live</span>
            </div>
            <div class="text-secondary">Apel Pagi Hari/Tanggal: {{ $formattedDate }}</div>
        </div>
        @if ($isAdminView)
            <a class="btn btn-sm btn-outline-primary align-self-start no-print" href="{{ route('recap.index', ['date' => $date]) }}">Buka Rekap Cetak</a>
        @endif
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="border rounded p-3 bg-light">
                <div class="stat-label">Jumlah</div>
                <div class="stat-value" data-live-total>{{ $totalActive }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 bg-light">
                <div class="stat-label">Hadir</div>
                <div class="stat-value" data-live-hadir>{{ $hadir }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 bg-light">
                <div class="stat-label">Kurang</div>
                <div class="stat-value" data-live-kurang>{{ $kurang }}</div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle mb-3">
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
                    <td data-live-total>{{ $totalActive }}</td>
                    <td data-live-hadir>{{ $hadir }}</td>
                    <td data-live-kurang>{{ $kurang }}</td>
                    @foreach ($absenceStatuses as $status)
                        <td data-live-count="{{ $status }}">{{ $counts[$status] ?? 0 }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>

    <table class="table table-bordered mb-0">
        <tbody>
            <tr>
                <th style="width: 160px;">Keterangan</th>
                <td data-live-details>{{ $details }}</td>
            </tr>
        </tbody>
    </table>
</section>

<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
    <div>
        <h2 class="h5 mb-1">Input Cepat Absensi</h2>
        <div class="text-secondary">{{ $isAdminView ? 'Semua bidang' : ($bidangOptions[0] ?? '-') }} - {{ $formattedDate }}</div>
    </div>
    <div class="no-print" style="min-width: min(100%, 420px);">
        <input class="form-control form-control-lg" id="quickEmployeeSearch" type="search" placeholder="Cari nama pegawai..." autocomplete="off">
    </div>
</div>

<div class="text-center text-secondary py-3 d-none" id="quickNoResults">Nama tidak ditemukan.</div>

@foreach ($quickSections as $section)
    @php($sectionBidang = $section['bidang'])
    @php($sectionEmployees = $section['employees'])
    @php($sectionRecords = $section['records'])
    @php($sectionSubmission = $section['submission'])
    @php($sectionLocked = $sectionSubmission && ! $isAdminView)
    @php($sectionKey = 'quick-section-'.$loop->index)

    <section class="stat-card p-3 mb-4 quick-bidang-section" data-section data-bidang-section="{{ $sectionBidang }}" data-storage-key="bpad-section-{{ \Illuminate\Support\Str::slug($sectionBidang) }}-{{ $date }}">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-2 mb-3">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h3 class="h5 mb-0">{{ $sectionBidang }}</h3>
                    <button class="btn btn-sm btn-outline-secondary quick-section-toggle" type="button" aria-expanded="true">Minimize</button>
                </div>
                <div class="text-secondary">{{ $sectionEmployees->count() }} pegawai aktif</div>
            </div>
            <div class="d-flex flex-wrap gap-2 no-print">
                @if ($sectionSubmission)
                    <span class="badge text-bg-success align-self-center">Sudah Submit</span>
                @else
                    <span class="badge text-bg-secondary align-self-center">Belum Submit</span>
                @endif
                <a class="btn btn-sm btn-outline-primary" href="{{ route('attendance.index', ['bidang' => $sectionBidang]) }}">Input Detail</a>
            </div>
        </div>

        <div class="quick-bidang-body">
        @if ($sectionSubmission)
            <div class="alert alert-info no-print">
                Submit pada {{ $sectionSubmission->submitted_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}.
                {{ $isAdminView ? 'Admin dapat menyimpan ulang untuk koreksi.' : 'Koreksi setelah submit hanya dapat dilakukan oleh admin BPAD.' }}
            </div>
        @endif

        @if ($sectionEmployees->isEmpty())
            <div class="text-center text-secondary py-4">Tidak ada pegawai aktif pada bidang ini.</div>
        @else
            <form method="POST" action="{{ route('attendance.store') }}" class="quick-attendance-form" data-bidang="{{ $sectionBidang }}" data-modal-id="{{ $sectionKey }}-modal" data-confirm-summary-id="{{ $sectionKey }}-confirm-summary">
                @csrf
                <input type="hidden" name="attendance_date" value="{{ $date }}">
                <input type="hidden" name="bidang" value="{{ $sectionBidang }}">
                <input type="hidden" name="redirect_to" value="dashboard">

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 no-print">
                    <div class="d-flex flex-wrap gap-2" data-summary aria-live="polite"></div>
                    @unless ($sectionLocked)
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm btn-outline-success quick-set-all" type="button">Set Semua Hadir</button>
                            <button class="btn btn-sm btn-outline-primary quick-reset" type="button">Reset Data</button>
                        </div>
                    @endunless
                </div>

                <div class="quick-employee-list">
                    @foreach ($sectionEmployees as $employee)
                        @php($record = $sectionRecords->get($employee->id))
                        @php($selectedStatus = old('status.'.$employee->id, $record?->status ?? 'HADIR'))
                        <div class="quick-attendance-row attendance-status-row" data-name="{{ \Illuminate\Support\Str::lower($employee->displayName()) }}" data-label="{{ $employee->displayName() }}" data-search="{{ \Illuminate\Support\Str::lower($employee->displayName()) }}" data-selected-status="{{ $selectedStatus }}">
                            <div>
                                <div class="fw-semibold">
                                    {{ $employee->displayName() }}
                                </div>
                                <div class="selected-status-line" data-current-status>Status: {{ $statusOptions[$selectedStatus] ?? 'Hadir' }}</div>
                            </div>
                            <div class="status-button-group" data-original-status="{{ $record?->status ?? 'HADIR' }}">
                                @foreach ($statusOptions as $value => $label)
                                    @php($statusId = 'quick-'.$sectionKey.'-'.$employee->id.'-'.$value)
                                    @php($statusClass = 'status-'.\Illuminate\Support\Str::of($value)->lower()->replace('_', '-'))
                                    <input class="btn-check" type="radio" name="status[{{ $employee->id }}]" id="{{ $statusId }}" value="{{ $value }}" data-original-status="{{ $record?->status ?? 'HADIR' }}" @checked($selectedStatus === $value) @disabled($sectionLocked) required>
                                    <label class="status-choice {{ $statusClass }}" for="{{ $statusId }}">{{ $label }}</label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="sticky-submit-bar d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 no-print">
                    <div class="d-flex flex-wrap gap-2" data-summary-sticky aria-live="polite"></div>
                    <div class="d-flex justify-content-end gap-2">
                        @if ($sectionLocked)
                            <button class="btn btn-primary" type="button" disabled>Sudah Submit</button>
                        @else
                            <button class="btn btn-primary" type="submit">{{ $sectionSubmission ? 'Update '.$sectionBidang : 'Submit '.$sectionBidang }}</button>
                        @endif
                    </div>
                </div>
            </form>

            <div class="modal fade" id="{{ $sectionKey }}-modal" tabindex="-1" aria-labelledby="{{ $sectionKey }}-title" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title fs-5" id="{{ $sectionKey }}-title">Konfirmasi Submit</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="fw-semibold mb-2">{{ $sectionBidang }} - {{ $formattedDate }}</div>
                            <div id="{{ $sectionKey }}-confirm-summary"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary quick-confirm-submit">Ya, Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        </div>
    </section>
@endforeach

<div class="stat-card p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">{{ $isAdminView ? 'Status Submit 5 Bidang' : 'Status Submit Bidang' }}</h2>
        <a class="btn btn-sm btn-outline-primary no-print" href="{{ route('submissions.index') }}">Lihat Detail</a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <th>Bidang</th>
                    <th>Status</th>
                    <th>Waktu Submit</th>
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
                        <td>{{ $submission?->submitted_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const forms = Array.from(document.querySelectorAll('.quick-attendance-form'));

        if (forms.length === 0) {
            return;
        }

        const statusLabels = @json($statusOptions);
        const statusOrder = Object.keys(statusLabels);
        const absenceStatusOrder = @json($absenceStatuses);
        const search = document.getElementById('quickEmployeeSearch');
        const noResults = document.getElementById('quickNoResults');
        const dashboardToday = document.querySelector('[data-dashboard-today]');
        const dashboardClock = document.querySelector('[data-dashboard-clock]');
        const sectionStoragePrefix = 'bpad-section-state:';
        const sectionStorageAvailable = (() => {
            try {
                return !!window.localStorage;
            } catch (error) {
                return false;
            }
        })();

        const groups = (radios) => Array.from(new Set(radios.map((radio) => radio.name)));
        const allStatusRadios = () => Array.from(document.querySelectorAll('.quick-attendance-form input[type="radio"][name^="status["]'));
        const setText = (selector, value) => {
            document.querySelectorAll(selector).forEach((element) => {
                element.textContent = value;
            });
        };

        const totals = (radios) => {
            const counts = Object.fromEntries(statusOrder.map((status) => [status, 0]));

            radios.filter((radio) => radio.checked).forEach((radio) => {
                counts[radio.value] = (counts[radio.value] || 0) + 1;
            });

            return counts;
        };

        const renderLiveRecap = () => {
            const radios = allStatusRadios();
            const counts = totals(radios);
            const total = groups(radios).length;
            const hadir = counts.HADIR || 0;
            const kurang = Math.max(total - hadir, 0);
            const details = absenceStatusOrder
                .filter((status) => (counts[status] || 0) > 0)
                .map((status) => `${statusLabels[status]}: ${counts[status] || 0}`)
                .join(', ') || '-';

            setText('[data-live-total]', total);
            setText('[data-live-hadir]', hadir);
            setText('[data-live-kurang]', kurang);
            setText('[data-live-details]', details);

            absenceStatusOrder.forEach((status) => {
                setText(`[data-live-count="${status}"]`, counts[status] || 0);
            });
        };

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

        const updateDashboardClock = () => {
            const now = new Date();

            if (dashboardToday) {
                dashboardToday.textContent = formatterDate.format(now);
            }

            if (dashboardClock) {
                dashboardClock.textContent = `${formatterTime.format(now)} WITA`;
            }
        };

        const sectionKeyFor = (section) => `${sectionStoragePrefix}${section.dataset.storageKey || ''}`;

        const sectionBodyFor = (section) => section.querySelector('.quick-bidang-body');

        const sectionToggleButtonFor = (section) => section.querySelector('.quick-section-toggle');

        const setSectionCollapsed = (section, collapsed, persist = true) => {
            const body = sectionBodyFor(section);
            const toggle = sectionToggleButtonFor(section);

            if (!body || !toggle) {
                return;
            }

            body.classList.toggle('is-collapsed', collapsed);
            toggle.setAttribute('aria-expanded', String(!collapsed));
            toggle.textContent = collapsed ? 'Buka' : 'Minimize';

            if (persist && section.dataset.storageKey && sectionStorageAvailable) {
                try {
                    window.localStorage.setItem(sectionKeyFor(section), collapsed ? '1' : '0');
                } catch (error) {
                    // Storage unavailable; keep the UI working without persistence.
                }
            }
        };

        const restoreSectionState = (section) => {
            const stored = sectionStorageAvailable ? window.localStorage.getItem(sectionKeyFor(section)) : null;
            if (stored === '1') {
                setSectionCollapsed(section, true, false);
                return;
            }

            setSectionCollapsed(section, false, false);
        };

        const syncRows = (radios) => {
            radios.filter((radio) => radio.checked).forEach((radio) => {
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

        const renderSummary = (form, radios) => {
            const counts = totals(radios);
            const html = statusOrder
                .filter((status) => status === 'HADIR' || counts[status] > 0)
                .map((status) => `<span class="summary-pill">${statusLabels[status]}: <strong>${counts[status] || 0}</strong></span>`)
                .join('');

            form.querySelector('[data-summary]').innerHTML = html;
            form.querySelector('[data-summary-sticky]').innerHTML = html;
        };

        const refreshForm = (form) => {
            const radios = Array.from(form.querySelectorAll('input[type="radio"][name^="status["]'));

            syncRows(radios);
            renderSummary(form, radios);
            renderLiveRecap();
        };

        forms.forEach((form) => {
            const radios = Array.from(form.querySelectorAll('input[type="radio"][name^="status["]'));
            const modalElement = document.getElementById(form.dataset.modalId);
            const confirmSummary = document.getElementById(form.dataset.confirmSummaryId);
            const confirmModal = modalElement ? new bootstrap.Modal(modalElement) : null;

            radios.forEach((radio) => {
                radio.addEventListener('change', () => refreshForm(form));
            });

            form.querySelector('.quick-set-all')?.addEventListener('click', () => {
                groups(radios).forEach((name) => {
                    const present = radios.find((radio) => radio.name === name && radio.value === 'HADIR');

                    if (present) {
                        present.checked = true;
                    }
                });
                refreshForm(form);
            });

            form.querySelector('.quick-reset')?.addEventListener('click', () => {
                groups(radios).forEach((name) => {
                    const groupRadios = radios.filter((radio) => radio.name === name);
                    const original = groupRadios[0]?.dataset.originalStatus || 'HADIR';
                    const originalRadio = groupRadios.find((radio) => radio.value === original);

                    if (originalRadio) {
                        originalRadio.checked = true;
                    }
                });
                refreshForm(form);
            });

            form.addEventListener('submit', (event) => {
                if (form.dataset.confirmed === 'true' || !confirmModal) {
                    return;
                }

                event.preventDefault();

                const counts = totals(radios);
                const employeeCount = groups(radios).length;
                const rows = [
                    ['Jumlah', employeeCount],
                    ['Hadir', counts.HADIR || 0],
                    ['Kurang', employeeCount - (counts.HADIR || 0)],
                    ...statusOrder
                        .filter((status) => status !== 'HADIR' && counts[status] > 0)
                        .map((status) => [statusLabels[status], counts[status]]),
                ];

                confirmSummary.innerHTML = `<table class="table table-sm table-bordered mb-0"><tbody>${rows
                    .map(([label, value]) => `<tr><th>${label}</th><td class="text-end">${value}</td></tr>`)
                    .join('')}</tbody></table>`;
                confirmModal.show();
            });

            modalElement?.querySelector('.quick-confirm-submit')?.addEventListener('click', () => {
                form.dataset.confirmed = 'true';
                confirmModal?.hide();
                form.requestSubmit();
            });

            refreshForm(form);
        });

        const filterRows = () => {
            const term = (search?.value || '').trim().toLowerCase();
            let totalVisible = 0;

            document.querySelectorAll('.quick-bidang-section').forEach((section) => {
                let sectionVisible = 0;

                section.querySelectorAll('.quick-attendance-row').forEach((row) => {
                    const matches = (row.dataset.search || row.dataset.name || '').includes(term);
                    row.classList.toggle('d-none', !matches);

                    if (matches) {
                        sectionVisible++;
                        totalVisible++;
                    }
                });

                section.classList.toggle('d-none', term !== '' && sectionVisible === 0);

                if (term === '') {
                    restoreSectionState(section);
                } else if (sectionVisible > 0) {
                    setSectionCollapsed(section, false, false);
                }
            });

            noResults?.classList.toggle('d-none', totalVisible > 0);
        };

        search?.addEventListener('input', filterRows);
        filterRows();
        updateDashboardClock();
        window.setInterval(updateDashboardClock, 1000);

        document.querySelectorAll('.quick-bidang-section').forEach((section) => {
            restoreSectionState(section);
            sectionToggleButtonFor(section)?.addEventListener('click', () => {
                const body = sectionBodyFor(section);
                if (!body) {
                    return;
                }

                const isCollapsed = body.classList.contains('is-collapsed');
                setSectionCollapsed(section, !isCollapsed);
            });
        });

    })();
</script>
@endpush
