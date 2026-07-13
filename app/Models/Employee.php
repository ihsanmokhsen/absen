<?php

namespace App\Models;

use App\Support\AttendanceMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'nip',
        'bidang',
        'sort_order',
        'is_pppk',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'is_pppk' => 'boolean',
        ];
    }

    public function displayName(): string
    {
        return $this->name.($this->is_pppk ? ' (PPPK)' : '');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @return list<string>
     */
    public static function bidangOptions(): array
    {
        return AttendanceMeta::bidang();
    }
}
