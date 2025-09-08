<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class StaffController extends Controller
{
    public function index()
    {
        $users = User::where('role', '!=', 'admin')->get();

        return view('admin.staff-list', compact('users'));
    }

    public function monthly($user_id, $year = null, $month = null)
    {
        $user = User::findOrFail($user_id);

        $currentDate = \Carbon\Carbon::createFromDate(
            $year ?? now()->year,
            $month ?? now()->month,
            1
        );

        $firstDay = $currentDate->copy()->startOfMonth();
        $lastDay = $currentDate->copy()->endOfMonth();

        $yearMonthLabel = $currentDate->format('Y年m月');

        $attendances = \App\Models\Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$firstDay, $lastDay])
            ->orderBy('work_date')
            ->get()
            ->keyBy(function ($item) {
                return \Carbon\Carbon::parse($item->work_date)->format('Y-m-d');
            });

        return view('admin.staff-attendance', compact(
            'user',
            'attendances',
            'yearMonthLabel',
            'currentDate',
            'firstDay',
            'lastDay'
        ));
    }

    public function exportCsv(Request $request, $user_id)
    {
        $year  = $request->input('year');
        $month = $request->input('month');

        $user = User::findOrFail($user_id);

        $firstDay = Carbon::create($year, $month, 1);
        $lastDay  = $firstDay->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$firstDay->toDateString(), $lastDay->toDateString()])
            ->orderBy('work_date')
            ->get()
            ->keyBy(fn($att) => $att->work_date); // 日付文字列でキー化

        return response()->streamDownload(function () use ($attendances, $firstDay, $lastDay) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩(分)', '合計']);

            for ($date = $firstDay->copy(); $date->lte($lastDay); $date->addDay()) {
                $key = $date->format('Y-m-d');
                $att = $attendances->get($key);

                $clockIn  = $att->clock_in ?? '';
                $clockOut = $att->clock_out ?? '';
                $break    = $att->break_time ?? '';
                $total    = '';

                if ($clockIn && $clockOut) {
                    $start = Carbon::createFromFormat('H:i:s', $clockIn);
                    $end   = Carbon::createFromFormat('H:i:s', $clockOut);
                    $total = $end->diffInMinutes($start) - $break;
                }

                fputcsv($handle, [
                    $date->format('Y/m/d (D)'),
                    $clockIn,
                    $clockOut,
                    $break,
                    $total,
                ]);
            }

            fclose($handle);
        }, $user->name . "_attendance_" . $year . "_" . $month . ".csv", [
            'Content-Type' => 'text/csv; charset=Shift-JIS',
        ]);
    }
}
