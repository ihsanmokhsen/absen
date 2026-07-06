<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class AttendanceMeta
{
    public const BIDANG = [
        'SEKRETARIAT',
        'PENDAPATAN 1',
        'PENDAPATAN 2',
        'ASET 1',
        'ASET 2',
    ];

    public const STATUSES = [
        'HADIR' => 'Hadir',
        'IZIN_PAGI' => 'Izin',
        'SAKIT' => 'Sakit',
        'TUGAS' => 'Tugas',
        'TUBEL' => 'Tubel',
        'CUTI' => 'Cuti',
        'TERLAMBAT' => 'Terlambat',
    ];

    public const ABSENCE_STATUSES = [
        'CUTI',
        'IZIN_PAGI',
        'SAKIT',
        'TUGAS',
        'TUBEL',
        'TERLAMBAT',
    ];

    /**
     * @return list<string>
     */
    public static function bidang(): array
    {
        return self::BIDANG;
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return self::STATUSES;
    }

    /**
     * @return list<string>
     */
    public static function statusKeys(): array
    {
        return array_keys(self::STATUSES);
    }

    /**
     * @return list<string>
     */
    public static function absenceStatusKeys(): array
    {
        return self::ABSENCE_STATUSES;
    }

    public static function defaultDate(): string
    {
        return CarbonImmutable::now(config('app.timezone'))->toDateString();
    }

    public static function normalizeDate(?string $date): string
    {
        if (! $date) {
            return self::defaultDate();
        }

        return CarbonImmutable::parse($date, config('app.timezone'))->toDateString();
    }

    public static function resolveActiveDate(Request $request, ?string $date = null): string
    {
        if ($date) {
            $activeDate = self::normalizeDate($date);
            $request->session()->put('active_attendance_date', $activeDate);

            return $activeDate;
        }

        return self::normalizeDate($request->session()->get('active_attendance_date'));
    }

    public static function formatDate(?string $date): string
    {
        return CarbonImmutable::parse(self::normalizeDate($date), config('app.timezone'))
            ->locale('id')
            ->translatedFormat('l, d F Y');
    }
}
