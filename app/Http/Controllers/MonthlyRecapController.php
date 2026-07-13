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

        if (($validated['export'] ?? null) === 'csv') {
            return $this->exportCsv($rows, $month, $start, $statusOptions, $absenceStatuses);
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
        ]);
    }

    private function exportCsv($rows, string $month, CarbonImmutable $start, array $statusOptions, array $absenceStatuses): StreamedResponse
    {
        $filename = 'rekap-bulanan-absen-'.$month.'.csv';

        return response()->streamDownload(function () use ($rows, $start, $statusOptions, $absenceStatuses): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Rekap Bulanan Absensi Apel Pagi BPAD Provinsi NTT']);
            fputcsv($handle, ['Bulan', $start->locale('id')->translatedFormat('F Y')]);
            fputcsv($handle, []);
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
