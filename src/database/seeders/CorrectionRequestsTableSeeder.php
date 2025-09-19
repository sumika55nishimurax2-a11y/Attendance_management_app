<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;
use Carbon\Carbon;

class CorrectionRequestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {

            // 既存勤怠に対する修正申請を作成（例：当月の最終勤務日）
            $attendance = Attendance::where('user_id', $user->id)
                ->orderBy('work_date', 'desc')
                ->first();

            if ($attendance) {
                // 勤怠の出勤時間修正申請
                CorrectionRequest::create([
                    'user_id' => $user->id,
                    'attendance_id' => $attendance->id,
                    'field' => 'clock_in',
                    'break_id' => null,
                    'before_value' => $attendance->clock_in,
                    'after_value' => '08:50:00',
                    'reason' => '出勤時間修正テスト',
                    'requested_at' => Carbon::now(),
                    'status' => 'pending',
                    'approver_id' => null,
                ]);

                // 休憩終了時間修正申請（複数休憩対応例）
                $break = $attendance->breaks()->first();
                if ($break) {
                    CorrectionRequest::create([
                        'user_id' => $user->id,
                        'attendance_id' => $attendance->id,
                        'field' => 'break_end',
                        'break_id' => $break->id,
                        'before_value' => $break->break_end,
                        'after_value' => '12:45:00',
                        'reason' => '休憩終了時間修正テスト',
                        'requested_at' => Carbon::now(),
                        'status' => 'pending',
                        'approver_id' => null,
                    ]);
                }
            }

            // 新規勤怠用の申請（attendance はまだ作られていない）
            $futureDate = Carbon::now()->addDay();
            $newAttendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => $futureDate->format('Y-m-d'),
            ]);

            CorrectionRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $newAttendance->id,
                'field' => 'clock_in',
                'break_id' => null,
                'before_value' => null,
                'after_value' => '09:10:00',
                'reason' => '新規勤怠申請テスト',
                'requested_at' => Carbon::now(),
                'status' => 'pending',
                'approver_id' => null,
            ]);
        }
    }
}
