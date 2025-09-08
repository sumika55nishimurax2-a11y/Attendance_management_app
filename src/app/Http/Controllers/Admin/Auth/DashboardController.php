<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;


class DashboardController extends Controller
{
    public function index($date = null)
    {
        $currentDate = $date ? Carbon::parse($date) : Carbon::today();

        $users = User::where('role', '!=', 'admin')
            ->orderBy('name')
            ->get();

        $attendances = Attendance::whereDate('work_date', $currentDate)->get()->keyBy('user_id');

        return view('admin.admin-list', compact('currentDate', 'users', 'attendances'));
    }

    public function show(Request $request, $id = null)
    {
        if ($id) {
            $attendance = Attendance::with('user')->findOrFail($id);
            $date = $attendance->work_date ? Carbon::parse($attendance->work_date) : Carbon::today();
        } else {
            $userId = $request->input('user_id');
            $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();
            $attendance = new Attendance([
                'user_id'   => $userId,
                'work_date' => $date,
            ]);
            $attendance->setRelation('user', User::find($userId));
        }

        return view('admin.admin-detail', compact('attendance', 'date'));
    }

    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // バリデーション
        $validated = $request->validate([
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'breaks.*.start' => 'nullable|date_format:H:i',
            'breaks.*.end'   => 'nullable|date_format:H:i|after:breaks.*.start',
            'note' => 'nullable|string|max:500',
        ]);

        // 出退勤
        $attendance->clock_in  = $validated['clock_in'] ?? null;
        $attendance->clock_out = $validated['clock_out'] ?? null;

        // 備考
        $attendance->note = $validated['note'] ?? null;

        // 休憩処理
        $totalBreakMinutes = 0;
        $attendance->breaks()->delete();

        if (isset($validated['breaks'])) {
            foreach ($validated['breaks'] as $break) {
                if (!empty($break['start']) && !empty($break['end'])) {
                    $start = Carbon::createFromFormat('H:i', $break['start']);
                    $end   = Carbon::createFromFormat('H:i', $break['end']);
                    $duration = $end->diffInMinutes($start);

                    $attendance->breaks()->create([
                        'break_start'      => $start,
                        'break_end'        => $end,
                        'duration_minutes' => $duration,
                    ]);

                    $totalBreakMinutes += $duration;
                }
            }
        }

        $attendance->break_time = $totalBreakMinutes;
        $attendance->save();

        return redirect()->route('admin.attendance.detail', ['id' => $attendance->id])
            ->with('status', '勤怠を修正しました。');
    }

    public function store(AttendanceRequest $request)
    {
        $validated = $request->validate([
            'user_id'   => 'required|exists:users,id',
            'work_date' => 'required|date',
            'clock_in'  => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'breaks.*.start' => 'nullable|date_format:H:i',
            'breaks.*.end'   => 'nullable|date_format:H:i|after:breaks.*.start',
            'note' => 'nullable|string|max:500',
        ]);

        // 勤怠本体作成
        $attendance = Attendance::create([
            'user_id'   => $validated['user_id'],
            'work_date' => $validated['work_date'],
            'clock_in'  => $validated['clock_in'] ?? null,
            'clock_out' => $validated['clock_out'] ?? null,
            'note'      => $validated['note'] ?? null,
        ]);

        // 休憩処理
        $totalBreakMinutes = 0;
        if (isset($validated['breaks'])) {
            foreach ($validated['breaks'] as $break) {
                if (!empty($break['start']) && !empty($break['end'])) {
                    $start = Carbon::createFromFormat('H:i', $break['start']);
                    $end   = Carbon::createFromFormat('H:i', $break['end']);
                    $duration = $end->diffInMinutes($start);

                    $attendance->breaks()->create([
                        'break_start'      => $start,
                        'break_end'        => $end,
                        'duration_minutes' => $duration,
                    ]);

                    $totalBreakMinutes += $duration;
                }
            }
        }

        // 合計休憩時間を反映
        $attendance->break_time = $totalBreakMinutes;
        $attendance->save();

        return redirect()->route('admin.attendance.detail', ['id' => $attendance->id])
            ->with('status', '勤怠を新規登録しました。');
    }
}
