<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSubmission;
use App\Support\AttendanceMeta;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusSubmitController extends Controller
{
    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $date = AttendanceMeta::resolveActiveDate($request, $validated['date'] ?? null);
        $bidangOptions = $request->user()->allowedBidang();
        $submissions = AttendanceSubmission::query()
            ->with('submittedBy')
            ->whereDate('attendance_date', $date)
            ->whereIn('bidang', $bidangOptions)
            ->get()
            ->keyBy('bidang');

        return view('submissions.index', [
            'date' => $date,
            'formattedDate' => AttendanceMeta::formatDate($date),
            'bidangOptions' => $bidangOptions,
            'submissions' => $submissions,
            'isAdminView' => $request->user()->isAdmin(),
        ]);
    }
}
