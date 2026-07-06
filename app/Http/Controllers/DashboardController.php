<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSubmission;
use App\Models\Employee;
use App\Support\AttendanceMeta;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $date = AttendanceMeta::resolveActiveDate($request, $validated['date'] ?? null);
        $user = $request->user();
        $bidangOptions = $user->allowedBidang();
        $totalActive = Employee::active()
            ->when(! $user->isAdmin(), fn ($query) => $query->whereIn('bidang', $bidangOptions))
            ->count();
        $submissions = AttendanceSubmission::query()
            ->whereDate('attendance_date', $date)
            ->whereIn('bidang', $bidangOptions)
            ->get()
            ->keyBy('bidang');
        $quickEmployees = Employee::active()
            ->whereIn('bidang', $bidangOptions)
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $employeesByBidang = $quickEmployees->groupBy('bidang');
        $records = AttendanceRecord::query()
            ->whereDate('attendance_date', $date)
            ->whereIn('employee_id', $quickEmployees->pluck('id'))
            ->get()
            ->keyBy('employee_id');
        $counts = array_fill_keys(AttendanceMeta::statusKeys(), 0);

        foreach ($quickEmployees as $employee) {
            $status = $records->get($employee->id)?->status ?? 'HADIR';
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }

        $hadir = $counts['HADIR'];
        $kurang = max($totalActive - $hadir, 0);
        $details = collect(AttendanceMeta::absenceStatusKeys())
            ->map(fn ($status) => [
                'label' => AttendanceMeta::statuses()[$status],
                'total' => $counts[$status] ?? 0,
            ])
            ->filter(fn ($item) => $item['total'] > 0)
            ->map(fn ($item) => $item['label'].': '.$item['total'])
            ->implode(', ');
        $quickSections = collect($bidangOptions)
            ->map(fn ($bidang) => [
                'bidang' => $bidang,
                'employees' => $employeesByBidang->get($bidang, collect()),
                'records' => $records,
                'submission' => $submissions->get($bidang),
            ]);

        return view('dashboard', [
            'date' => $date,
            'formattedDate' => AttendanceMeta::formatDate($date),
            'bidangOptions' => $bidangOptions,
            'totalActive' => $totalActive,
            'hadir' => $hadir,
            'kurang' => $kurang,
            'counts' => $counts,
            'details' => $details ?: '-',
            'submissions' => $submissions,
            'isAdminView' => $user->isAdmin(),
            'statusOptions' => AttendanceMeta::statuses(),
            'absenceStatuses' => AttendanceMeta::absenceStatusKeys(),
            'quickSections' => $quickSections,
        ]);
    }
}
