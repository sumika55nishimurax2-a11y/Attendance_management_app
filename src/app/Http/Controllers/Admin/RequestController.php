<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CorrectionRequest;
use App\Models\Attendance;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        // 承認待ち一覧
        $pendingRequests = CorrectionRequest::with(['attendance.user', 'user'])
            ->where('status', 'pending')
            ->whereNotNull('requested_at')
            ->orderBy('requested_at', 'desc')
            ->paginate(10, ['*'], 'pending_page');

        // 承認済み一覧
        $approvedRequests = CorrectionRequest::with(['attendance.user', 'user'])
            ->where('status', 'approved')
            ->whereNotNull('requested_at')
            ->orderBy('requested_at', 'desc')
            ->paginate(10, ['*'], 'approved_page');

        return view('admin.request-list', compact('pendingRequests', 'approvedRequests'));
    }

    public function showApprove(CorrectionRequest $attendance_correction_request)
    {
        $attendance = Attendance::with(['correctionRequests' => function ($q) {
            $q->orderByDesc('requested_at')->orderByDesc('created_at');
        }])->find($attendance_correction_request->attendance_id);

        if (!$attendance) {
            $attendance = new Attendance([
                'user_id'   => $attendance_correction_request->attendance?->user_id
                    ?? $attendance_correction_request->user_id,
                'work_date' => $attendance_correction_request->work_date ?? now()->format('Y-m-d'),
                'clock_in'  => null,
                'clock_out' => null,
                'break_time' => 0,
                'note'      => null,
            ]);
            $attendance->breaks = collect();
        }

        $displayAttendance = $attendance->replicate();

        foreach ($attendance_correction_request->after_value ? [$attendance_correction_request] : [] as $req) {
            $field = $req->field;
            $after = $req->after_value;
            if (is_null($after)) continue;

            if (in_array($field, ['clock_in', 'clock_out'])) {
                $displayAttendance->{$field} = $after;
            } elseif (in_array($field, ['break_start', 'break_end'])) {

                $breakIndex = $displayAttendance->breaks->search(function ($b) use ($req) {
                    return $b->id === $req->break_id;
                });

                if ($breakIndex !== false) {
                    $displayAttendance->breaks[$breakIndex]->{$field} = $after;
                }
            }
        }

        return view('admin.approval', [
            'attendance'        => $attendance,
            'correctionRequest' => $attendance_correction_request,
            'displayAttendance' => $displayAttendance,
            'reason'            => $attendance_correction_request->reason,
        ]);
    }

    public function approve(CorrectionRequest $correctionRequest)
    {
        // 勤怠データ取得、存在しなければ新規作成
        $attendance = $correctionRequest->attendance;

        if (!$attendance) {
            $attendance = Attendance::create([
                'user_id'   => $correctionRequest->user_id,
                'work_date' => $correctionRequest->work_date ?? now()->format('Y-m-d'),
            ]);
        }

        // 保留中の申請を全て取得
        $requests = $attendance->correctionRequests()->where('status', 'pending')->get();

        foreach ($requests as $req) {
            $after = $req->after_value;
            if (is_null($after)) continue;

            switch ($req->field) {
                case 'clock_in':
                case 'clock_out':
                    $attendance->{$req->field} = $after;
                    break;

                case 'break_start':
                case 'break_end':
                    // 複数の休憩に対応
                    $break = $attendance->breaks()->find($req->break_id);

                    if ($break) {
                        // 既存 break の更新
                        $break->{$req->field} = $after;
                        $break->save();
                    } else {
                        // break_id が無い場合は新規作成
                        $attendance->breaks()->create([
                            'id' => $req->break_id,
                            $req->field => $after,
                        ]);
                    }
                    break;
            }

            // 申請ステータスを承認済みに更新
            $req->status = 'approved';
            $req->approver_id = auth()->id();
            $req->save();
        }

        // 勤怠本体保存
        $attendance->save();

        return redirect()->back()->with('success', '承認が完了しました。');
    }
}
