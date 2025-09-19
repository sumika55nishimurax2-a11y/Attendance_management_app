<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $month = $request->input('month') ?? Carbon::now()->format('Y-m');
        $firstDay = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $lastDay  = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$firstDay->format('Y-m-d'), $lastDay->format('Y-m-d')])
            ->get()
            ->keyBy(function ($item) {
                return $item->work_date->format('Y-m-d');
            });

        $prevMonth = $firstDay->copy()->subMonth()->format('Y-m');
        $nextMonth = $firstDay->copy()->addMonth()->format('Y-m');

        return view('staff.list', [
            'user' => $user,
            'firstDay' => $firstDay,
            'lastDay' => $lastDay,
            'attendances' => $attendances,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    public function show(Request $request, $id = null)
    {
        if ($id) {
            $attendance = Attendance::with('correctionRequests')->findOrFail($id);
            // 最新の修正申請を取得
            $latestRequest = $attendance->correctionRequests()->latest()->first();
        } else {
            $date = $request->input('date');
            $attendance = new Attendance([
                'user_id'   => Auth::id(),
                'work_date' => $date,
            ]);
            $latestRequest = null;
        }

        return view('staff.detail', [
            'attendance' => $attendance,
            'latestRequest' => $latestRequest, // Bladeで使う
        ]);
    }

    public function update(AttendanceRequest $request, $id)
    {
        // ID がある場合は既存取得、無ければ新規作成
        $attendance = Attendance::find($id) ?? new Attendance();

        // 新規の場合は user_id と work_date をセット
        if (!$attendance->exists) {
            $attendance->user_id   = Auth::id();
            $attendance->work_date = $request->input('work_date');
        }

        // 編集不可チェック（既存のみ）
        if ($attendance->exists && !$attendance->is_editable) {
            return redirect()->back()->with('error', '承認待ちのため修正できません');
        }

        // 保存前の値を取得（既存の場合のみ）
        $beforeValues = $attendance->exists ? [
            'clock_in'  => $attendance->clock_in?->format('H:i'),
            'clock_out' => $attendance->clock_out?->format('H:i'),
            'note'      => $attendance->note,
        ] : [];

        // 入力値をセット
        $attendance->clock_in  = $request->input('clock_in');
        $attendance->clock_out = $request->input('clock_out');
        $attendance->note      = $request->input('note');

        $attendance->save();

        // 修正申請／新規登録の申請レコード作成（過去日 or 既存編集のみ）
        if (($attendance->work_date != Carbon::today()->toDateString()) || ($attendance->exists && !empty($beforeValues))) {
            $attendance->correctionRequests()->create([
                'field'        => 'all',
                'before_value' => json_encode($beforeValues),
                'after_value'  => json_encode([
                    'clock_in'  => $attendance->clock_in,
                    'clock_out' => $attendance->clock_out,
                    'note'      => $attendance->note,
                ]),
                'reason'       => $request->input('reason', $attendance->exists ? '修正申請' : '新規勤怠登録'),
                'requested_at' => now(),
                'status'       => \App\Models\CorrectionRequest::STATUS_PENDING,
            ]);
        }
        // 休憩時間の保存
        $breaks = $request->input('breaks', []);

        // 既存休憩を削除
        $attendance->breaks()->delete();

        foreach ($breaks as $break) {
            $start = $break['start'] ?? null;
            $end   = $break['end'] ?? null;

            if (!$start && !$end) continue;

            $startTime = $start ? Carbon::parse($start) : null;
            $endTime   = $end   ? Carbon::parse($end)   : null;
            $duration  = ($startTime && $endTime) ? $endTime->diffInMinutes($startTime) : 0;

            $attendance->breaks()->create([
                'break_start'      => $startTime,
                'break_end'        => $endTime,
                'duration_minutes' => $duration,
            ]);
        }

        // 合計休憩時間更新
        $attendance->updateBreakTotal();

        return redirect()->route('attendance.detail', ['id' => $attendance->id])
            ->with('status', $attendance->work_date == Carbon::today()->toDateString()
                ? '当日の勤怠を登録/更新しました。'
                : '過去日の勤怠を承認待ちとして登録しました。');
    }

    public function requestList()
    {
        $user = Auth::user();

        // 承認待ちリスト
        $pendingRequests = CorrectionRequest::with(['attendance.user'])
            ->whereHas('attendance', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('status', 'pending')
            ->orderBy('requested_at', 'desc')
            ->paginate(20, ['*'], 'pending_page');

        // 承認済みリスト
        $approvedRequests = CorrectionRequest::with(['attendance.user'])
            ->whereHas('attendance', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('status', 'approved')
            ->orderBy('requested_at', 'desc')
            ->paginate(20, ['*'], 'approved_page');

        return view('staff.request', compact('pendingRequests', 'approvedRequests'));
    }
}
