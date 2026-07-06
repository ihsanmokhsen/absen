<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSubmission;
use App\Models\Employee;
use App\Support\AttendanceMeta;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecapController extends Controller
{
    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $date = AttendanceMeta::resolveActiveDate($request, $validated['date'] ?? null);
        $counts = array_fill_keys(AttendanceMeta::statusKeys(), 0);
        $recordCounts = AttendanceRecord::query()
            ->selectRaw('status, COUNT(*) as total')
            ->whereDate('attendance_date', $date)
            ->whereHas('employee', fn ($query) => $query->active())
            ->groupBy('status')
            ->pluck('total', 'status');

        foreach ($recordCounts as $status => $total) {
            $counts[$status] = (int) $total;
        }

        $totalActive = Employee::active()->count();
        $hadir = $counts['HADIR'];
        $kurang = max($totalActive - $hadir, 0);
        $submissions = AttendanceSubmission::query()
            ->whereDate('attendance_date', $date)
            ->get()
            ->keyBy('bidang');
        $allSubmitted = $submissions->count() === count(AttendanceMeta::bidang());
        $details = collect(AttendanceMeta::absenceStatusKeys())
            ->map(fn ($status) => [
                'label' => AttendanceMeta::statuses()[$status],
                'total' => $counts[$status] ?? 0,
            ])
            ->filter(fn ($item) => $item['total'] > 0)
            ->map(fn ($item) => $item['label'].': '.$item['total'])
            ->implode(', ');
        $absenceRecords = AttendanceRecord::query()
            ->with('employee')
            ->join('employees', 'employees.id', '=', 'attendance_records.employee_id')
            ->select('attendance_records.*')
            ->whereDate('attendance_records.attendance_date', $date)
            ->where('attendance_records.status', '!=', 'HADIR')
            ->where('employees.is_active', true)
            ->orderByRaw('employees.sort_order is null')
            ->orderBy('employees.sort_order')
            ->orderBy('employees.name')
            ->get()
            ->groupBy('status');

        return view('recap.index', [
            'date' => $date,
            'formattedDate' => AttendanceMeta::formatDate($date),
            'bidangOptions' => AttendanceMeta::bidang(),
            'submissions' => $submissions,
            'allSubmitted' => $allSubmitted,
            'statusOptions' => AttendanceMeta::statuses(),
            'absenceStatuses' => AttendanceMeta::absenceStatusKeys(),
            'counts' => $counts,
            'totalActive' => $totalActive,
            'hadir' => $hadir,
            'kurang' => $kurang,
            'details' => $details ?: '-',
            'absenceRecords' => $absenceRecords,
        ]);
    }
}
