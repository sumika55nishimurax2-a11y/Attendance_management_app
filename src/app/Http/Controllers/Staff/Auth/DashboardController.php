<?php

namespace App\Http\Controllers\Staff\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // メール未認証なら認証画面へ
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // 初回ログインなら last_login_at を更新
        if (!$user->last_login_at) {
            $user->last_login_at = now();
            $user->save();
        }

        Carbon::setLocale('ja');
        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if (!$attendance) {
            $status = 'before_work';
        } elseif (!$attendance->clock_out) {
            $lastBreak = $attendance->breaks()->latest()->first();
            $status = ($lastBreak && !$lastBreak->break_end) ? 'on_break' : 'working';
        } else {
            $status = 'after_work';
        }

        return view('staff.attendance', compact('status'));
    }

    public function startWork()
    {
        $today = now()->toDateString();

        // すでに当日の勤怠があるか確認
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', $today)
            ->first();

        if ($attendance) {
            return redirect()->route('attendance')
                ->with('error', '本日は既に出勤済みです');
        }

        // なければ新規作成
        Attendance::create([
            'user_id'    => Auth::id(),
            'work_date'  => $today,
            'clock_in'   => now()->format('H:i:s'),
            'break_time' => 0,
        ]);

        return redirect()->route('attendance')
            ->with('status', '出勤打刻しました');
    }

    public function finishWork()
    {
        $attendance = $this->getTodayAttendance();

        if ($attendance->break_time_flag && $attendance->break_start_time) {
            $attendance->break_time += $this->calculateBreakMinutes($attendance->break_start_time);
        }

        $attendance->update([
            'clock_out' => Carbon::now()->format('H:i:s'),
            'break_time_flag' => false,
            'break_start_time' => null,
        ]);

        return redirect()->route('attendance');
    }

    public function startBreak()
    {
        $attendance = $this->getTodayAttendance();

        // 新しい休憩レコードを作成
        $attendance->breaks()->create([
            'break_start' => now(),
            'break_end' => null,
            'duration_minutes' => 0,
        ]);

        return redirect()->route('attendance');
    }

    public function endBreak()
    {
        $attendance = $this->getTodayAttendance();

        // 最新の休憩レコードを取得
        $break = $attendance->breaks()->latest()->first();

        if ($break && !$break->break_end) {
            $break->update([
                'break_end' => now(),
                'duration_minutes' => now()->diffInMinutes($break->break_start),
            ]);
        }
        return redirect()->route('attendance');
    }

    private function getTodayAttendance()
    {
        return Attendance::where('user_id', Auth::id())
            ->whereDate('work_date', Carbon::today())
            ->firstOrFail();
    }

    private function calculateBreakMinutes($breakStart)
    {
        return intdiv(
            Carbon::parse($breakStart)->diffInSeconds(Carbon::now()),
            60
        );
    }
}
