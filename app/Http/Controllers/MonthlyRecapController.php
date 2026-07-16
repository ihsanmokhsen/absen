<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSubmission;
use App\Models\Employee;
use App\Support\AttendanceMeta;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonthlyRecapController extends Controller
{
    public function __invoke(Request $request): View|StreamedResponse
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'export' => ['nullable', 'in:csv'],
        ]);

        $month = $validated['month'] ?? CarbonImmutable::now(config('app.timezone'))->format('Y-m');
        $start = CarbonImmutable::createFromFormat('Y-m-d', $month.'-01', config('app.timezone'))->startOfMonth();
        $end = $start->endOfMonth();
        $statusOptions = AttendanceMeta::statuses();
        $absenceStatuses = AttendanceMeta::absenceStatusKeys();

        $completeDates = AttendanceSubmission::query()
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->select('attendance_date')
            ->groupBy('attendance_date')
            ->havingRaw('COUNT(DISTINCT bidang) = ?', [count(AttendanceMeta::bidang())])
            ->pluck('attendance_date')
            ->map(fn ($date) => $date->toDateString());
        $submissions = AttendanceSubmission::query()
            ->whereIn('attendance_date', $completeDates)
            ->orderBy('attendance_date')
            ->get();
        $submittedDaysByBidang = $submissions
            ->groupBy('bidang')
            ->map(fn ($items) => $items->pluck('attendance_date')->map->toDateString()->unique()->count());

        $bidangOrder = collect(AttendanceMeta::bidang())
            ->map(fn (string $bidang, int $index) => "WHEN bidang = '".str_replace("'", "''", $bidang)."' THEN ".$index)
            ->implode(' ');
        $employees = Employee::active()
            ->orderByRaw('CASE '.$bidangOrder.' ELSE '.count(AttendanceMeta::bidang()).' END')
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $countsByEmployee = AttendanceRecord::query()
            ->join('employees', 'employees.id', '=', 'attendance_records.employee_id')
            ->join('attendance_submissions', function ($join): void {
                $join->on('attendance_submissions.bidang', '=', 'employees.bidang')
                    ->on('attendance_submissions.attendance_date', '=', 'attendance_records.attendance_date');
            })
            ->select('attendance_records.employee_id', 'attendance_records.status', DB::raw('COUNT(*) as total'))
            ->whereBetween('attendance_records.attendance_date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('attendance_records.attendance_date', $completeDates)
            ->where('employees.is_active', true)
            ->groupBy('attendance_records.employee_id', 'attendance_records.status')
            ->get()
            ->groupBy('employee_id');

        $rows = $employees->map(function (Employee $employee) use ($countsByEmployee, $statusOptions, $submittedDaysByBidang) {
            $counts = array_fill_keys(array_keys($statusOptions), 0);

            foreach ($countsByEmployee->get($employee->id, collect()) as $item) {
                $counts[$item->status] = (int) $item->total;
            }

            $submittedDays = (int) ($submittedDaysByBidang->get($employee->bidang) ?? 0);

            return [
                'employee' => $employee,
                'submitted_days' => $submittedDays,
                'counts' => $counts,
            ];
        });

        $summary = [
            'submitted_days' => $submissions->pluck('attendance_date')->map->toDateString()->unique()->count(),
            'submitted_fields' => $submissions->count(),
            'employees' => $employees->count(),
        ];
        $statusTotals = collect($absenceStatuses)
            ->mapWithKeys(fn (string $status) => [
                $status => $rows->sum(fn (array $row) => $row['counts'][$status] ?? 0),
            ]);
        $withoutNewsRows = $rows
            ->map(function (array $row) use ($absenceStatuses): array {
                return $row + [
                    'without_news' => $row['counts']['TERLAMBAT'] ?? 0,
                    'absence_total' => collect($absenceStatuses)
                        ->sum(fn (string $status) => $row['counts'][$status] ?? 0),
                ];
            })
            ->filter(fn (array $row) => $row['without_news'] > 0)
            ->sortByDesc(fn (array $row) => ($row['without_news'] * 1000) + $row['absence_total'])
            ->values();
        $attentionRows = $withoutNewsRows->take(10);
        $withoutNewsByBidang = collect(AttendanceMeta::bidang())
            ->mapWithKeys(fn (string $bidang) => [
                $bidang => $rows
                    ->filter(fn (array $row) => $row['employee']->bidang === $bidang)
                    ->sum(fn (array $row) => $row['counts']['TERLAMBAT'] ?? 0),
            ]);
        $topBidang = $withoutNewsByBidang->sortDesc()->keys()->first();
        $insights = [
            'without_news_total' => (int) ($statusTotals->get('TERLAMBAT') ?? 0),
            'without_news_employees' => $withoutNewsRows->count(),
            'top_bidang' => ($withoutNewsByBidang->get($topBidang, 0) ?? 0) > 0 ? $topBidang : null,
            'top_bidang_total' => (int) ($withoutNewsByBidang->get($topBidang, 0) ?? 0),
            'attention_rows' => $attentionRows,
        ];

        if (($validated['export'] ?? null) === 'csv') {
            return $this->exportCsv($rows, $month, $start, $statusOptions, $absenceStatuses, $insights, $statusTotals);
        }

        return view('recap.monthly', [
            'month' => $month,
            'monthLabel' => $start->locale('id')->translatedFormat('F Y'),
            'generatedAt' => CarbonImmutable::now(config('app.timezone'))->locale('id')->translatedFormat('d F Y H:i'),
            'statusOptions' => $statusOptions,
            'absenceStatuses' => $absenceStatuses,
            'rows' => $rows,
            'summary' => $summary,
            'submittedDaysByBidang' => $submittedDaysByBidang,
            'bidangOptions' => AttendanceMeta::bidang(),
            'insights' => $insights,
            'statusTotals' => $statusTotals,
        ]);
    }

    private function exportCsv($rows, string $month, CarbonImmutable $start, array $statusOptions, array $absenceStatuses, array $insights, $statusTotals): StreamedResponse
    {
        $filename = 'rekap-bulanan-absen-'.$month.'.csv';

        return response()->streamDownload(function () use ($rows, $start, $statusOptions, $absenceStatuses, $insights, $statusTotals): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Rekap Bulanan Absensi Apel Pagi BPAD Provinsi NTT']);
            fputcsv($handle, ['Bulan', $start->locale('id')->translatedFormat('F Y')]);
            fputcsv($handle, []);
            fputcsv($handle, ['INSIGHT KEHADIRAN BULANAN']);
            fputcsv($handle, ['Total Tanpa Berita', $insights['without_news_total']]);
            fputcsv($handle, ['Pegawai dengan Tanpa Berita', $insights['without_news_employees']]);
            fputcsv($handle, ['Bidang Terbanyak Tanpa Berita', $insights['top_bidang'] ?? '-', $insights['top_bidang_total']]);
            foreach ($absenceStatuses as $status) {
                fputcsv($handle, ['Total '.$statusOptions[$status], $statusTotals->get($status, 0)]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['PRIORITAS TINDAK LANJUT TANPA BERITA']);
            fputcsv($handle, ['No', 'Nama Pegawai', 'Bidang', 'Tanpa Berita', 'Izin', 'Sakit', 'Cuti', 'Total Tidak Hadir']);
            foreach ($insights['attention_rows'] as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row['employee']->displayName(),
                    $row['employee']->bidang,
                    $row['without_news'],
                    $row['counts']['IZIN_PAGI'] ?? 0,
                    $row['counts']['SAKIT'] ?? 0,
                    $row['counts']['CUTI'] ?? 0,
                    $row['absence_total'],
                ]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['DATA STATUS SELURUH PEGAWAI']);
            fputcsv($handle, [
                'No',
                'Nama Pegawai',
                'Bidang',
                'Hari Submit Bidang',
                'Hadir',
                ...array_map(fn ($status) => $statusOptions[$status], $absenceStatuses),
            ]);

            foreach ($rows as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row['employee']->displayName(),
                    $row['employee']->bidang,
                    $row['submitted_days'],
                    $row['counts']['HADIR'] ?? 0,
                    ...array_map(fn ($status) => $row['counts'][$status] ?? 0, $absenceStatuses),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ], 'attachment');
    }
}
