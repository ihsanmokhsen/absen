<?php

use App\Models\AttendanceRecord;
use App\Models\AttendanceSubmission;
use App\Support\AttendanceMeta;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('attendance:purge-incomplete', function (): void {
    $incompleteDates = AttendanceSubmission::query()
        ->whereDate('attendance_date', '<', AttendanceMeta::defaultDate())
        ->select('attendance_date')
        ->groupBy('attendance_date')
        ->havingRaw('COUNT(DISTINCT bidang) < ?', [count(AttendanceMeta::bidang())])
        ->pluck('attendance_date')
        ->map(fn ($date) => $date->toDateString());

    if ($incompleteDates->isEmpty()) {
        $this->info('Tidak ada data absensi tidak lengkap.');

        return;
    }

    $deleted = DB::transaction(function () use ($incompleteDates): array {
        return [
            'records' => AttendanceRecord::query()->whereIn('attendance_date', $incompleteDates)->delete(),
            'submissions' => AttendanceSubmission::query()->whereIn('attendance_date', $incompleteDates)->delete(),
        ];
    });

    $this->info(sprintf(
        '%d tanggal tidak lengkap dihapus: %d record absensi dan %d submit.',
        $incompleteDates->count(),
        $deleted['records'],
        $deleted['submissions'],
    ));
})->purpose('Hapus seluruh absensi hari sebelumnya yang tidak lengkap 5 bidang');

Schedule::command('attendance:purge-incomplete')
    ->dailyAt('00:05')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();
