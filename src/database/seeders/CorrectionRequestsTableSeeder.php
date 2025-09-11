<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Carbon\Carbon;

class CorrectionRequestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attendances = Attendance::inRandomOrder()->take(10)->get();

        foreach ($attendances as $attendance) {
            $field = collect(['clock_in', 'clock_out', 'break_time'])->random();

            $before = null;
            $after = null;
            $reason = null;

            switch ($field) {
                case 'clock_in':
                    $before = $attendance->clock_in;
                    $after = Carbon::parse($attendance->clock_in)->subMinutes(10)->format('H:i:s');
                    $reason = '遅延のため';
                    break;

                case 'clock_out':
                    $before = $attendance->clock_out;
                    $after = Carbon::parse($attendance->clock_out)->addMinutes(20)->format('H:i:s');
                    $reason = '残業のため';
                    break;

                case 'break_time':
                    $before = $attendance->break_time;
                    $after = $attendance->break_time + 15;
                    $reason = '追加休憩申請のため';
                    break;
            }

            CorrectionRequest::create([
                'attendance_id' => $attendance->id,
                'field' => $field,
                'before_value' => $before,
                'after_value' => $after,
                'reason' => $reason,
                'requested_at' => now(),
                'status' => 'pending',
                'approver_id' => null,
            ]);
        }
    }
}
