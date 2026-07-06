<?php

namespace App\Models;

use App\Support\AttendanceMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'employee_id',
        'attendance_date',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date:Y-m-d',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return AttendanceMeta::statuses();
    }
}
