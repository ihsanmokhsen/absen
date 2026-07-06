<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSubmission;
use App\Models\Employee;
use App\Support\AttendanceMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'bidang' => ['nullable', Rule::in(AttendanceMeta::bidang())],
        ]);

        $date = AttendanceMeta::resolveActiveDate($request, $validated['date'] ?? null);
        $user = $request->user();
        $bidangOptions = $user->allowedBidang();

        if ($bidangOptions === []) {
            abort(403, 'Akun ini belum memiliki akses bidang.');
        }

        $bidang = $user->isAdmin()
            ? ($validated['bidang'] ?? $bidangOptions[0])
            : $bidangOptions[0];
        $employees = Employee::active()
            ->where('bidang', $bidang)
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $records = AttendanceRecord::query()
            ->whereDate('attendance_date', $date)
            ->whereIn('employee_id', $employees->pluck('id'))
            ->get()
            ->keyBy('employee_id');
        $submission = AttendanceSubmission::query()
            ->whereDate('attendance_date', $date)
            ->where('bidang', $bidang)
            ->first();

        return view('attendance.index', [
            'date' => $date,
            'formattedDate' => AttendanceMeta::formatDate($date),
            'bidang' => $bidang,
            'bidangOptions' => $bidangOptions,
            'statusOptions' => AttendanceMeta::statuses(),
            'employees' => $employees,
            'records' => $records,
            'submission' => $submission,
            'isAdminView' => $user->isAdmin(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'attendance_date' => ['required', 'date'],
            'bidang' => ['required', Rule::in(AttendanceMeta::bidang())],
            'status' => ['required', 'array'],
            'status.*' => ['required', Rule::in(AttendanceMeta::statusKeys())],
            'note' => ['nullable', 'array'],
            'note.*' => ['nullable', 'string', 'max:255'],
            'redirect_to' => ['nullable', Rule::in(['attendance', 'dashboard'])],
        ]);

        $date = AttendanceMeta::resolveActiveDate($request, $validated['attendance_date']);
        $bidang = $validated['bidang'];

        if (! $request->user()->canAccessBidang($bidang)) {
            abort(403, 'Akun ini hanya dapat mengisi absensi bidang '.$request->user()->bidang.'.');
        }

        if (! $request->user()->isAdmin()) {
            $alreadySubmitted = AttendanceSubmission::query()
                ->where('bidang', $bidang)
                ->whereDate('attendance_date', $date)
                ->exists();

            if ($alreadySubmitted) {
                return back()
                    ->withErrors(['bidang' => 'Bidang '.$bidang.' sudah submit untuk tanggal ini. Koreksi hanya dapat dilakukan oleh admin BPAD.'])
                    ->withInput();
            }
        }

        $employees = Employee::active()
            ->where('bidang', $bidang)
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $employeeIds = $employees->pluck('id')->map(fn ($id) => (string) $id)->all();
        $submittedIds = array_keys($validated['status']);
        $invalidIds = array_diff($submittedIds, $employeeIds);
        $missingIds = array_diff($employeeIds, $submittedIds);

        if ($invalidIds !== [] || $missingIds !== []) {
            return back()
                ->withErrors(['status' => 'Status absensi harus diisi untuk semua pegawai aktif pada bidang ini.'])
                ->withInput();
        }

        DB::transaction(function () use ($employees, $validated, $date, $bidang): void {
            foreach ($employees as $employee) {
                AttendanceRecord::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'attendance_date' => $date,
                    ],
                    [
                        'status' => $validated['status'][$employee->id],
                        'note' => $validated['note'][$employee->id] ?? null,
                    ],
                );
            }

            AttendanceSubmission::updateOrCreate(
                [
                    'bidang' => $bidang,
                    'attendance_date' => $date,
                ],
                [
                    'submitted_by' => Auth::id(),
                    'submitted_at' => now(),
                ],
            );
        });

        $route = ($validated['redirect_to'] ?? 'attendance') === 'dashboard'
            ? 'dashboard'
            : 'attendance.index';

        return redirect()
            ->route($route, ['bidang' => $bidang])
            ->with('success', 'Absensi bidang '.$bidang.' berhasil disimpan.');
    }
}
