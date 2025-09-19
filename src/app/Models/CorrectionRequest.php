<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Attendance;

class CorrectionRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING   = 'pending';    // 承認待ち
    public const STATUS_APPROVED  = 'approved';   // 承認済み
    public const STATUS_REJECTED  = 'rejected';   // 却下
    public const STATUS_CORRECTED = 'corrected';  // 修正済み

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_CORRECTED,
    ];

    protected $fillable = [
        'user_id',
        'attendance_id',
        'field',
        'before_value',
        'after_value',
        'reason',
        'requested_at',
        'status',
        'approver_id',
        'break_id',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class, 'attendance_id', 'id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function approve(int $approverId): void
    {
        $this->status = self::STATUS_APPROVED;
        $this->approver_id = $approverId;
        $this->save();

        // Attendance に反映
        if ($this->attendance && $this->field) {
            $this->attendance->update([
                $this->field => $this->after_value,
            ]);
        }

        $this->status = self::STATUS_CORRECTED;
        $this->save();
    }

    public function reject(int $approverId): void
    {
        $this->status = self::STATUS_REJECTED;
        $this->approver_id = $approverId;
        $this->save();
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getIsCorrectedAttribute(): bool
    {
        return $this->status === self::STATUS_CORRECTED;
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'pending'  => '承認待ち',
            'approved' => '承認済み',
            'rejected' => '否認',
            default    => '不明',
        };
    }
}
