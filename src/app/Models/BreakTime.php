<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
        'duration_minutes',
    ];
    protected $casts = [
        'break_start' => 'datetime:H:i',
        'break_end'   => 'datetime:H:i',
    ];


    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    protected static function booted()
    {
        static::saved(function ($break) {
            $attendance = $break->attendance;
            if ($attendance) {
                $total = $attendance->breaks()->sum(DB::raw('TIMESTAMPDIFF(MINUTE, break_start, break_end)'));
                $attendance->update(['break_time' => $total]);
            }
        });

        static::deleted(function ($break) {
            $attendance = $break->attendance;
            if ($attendance) {
                $total = $attendance->breaks()->sum(DB::raw('TIMESTAMPDIFF(MINUTE, break_start, break_end)'));
                $attendance->update(['break_time' => $total]);
            }
        });
    }
}
