<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {
            foreach ([Carbon::now()->subMonth(), Carbon::now()] as $month) {
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();

                for ($date = $start; $date->lte($end); $date->addDay()) {
                    if ($date->isWeekend()) {
                        continue;
                    }

                    $clockIn = Carbon::createFromTime(9, rand(0, 20));
                    $clockOut = Carbon::createFromTime(18, rand(0, 10));

                    $attendance = Attendance::create([
                        'user_id' => $user->id,
                        'work_date' => $date->format('Y-m-d'),
                        'clock_in' => $clockIn->format('H:i:s'),
                        'clock_out' => $clockOut->format('H:i:s'),
                        'break_time' => 60,
                        'note' => null,
                    ]);

                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start' => '12:00:00',
                        'break_end' => '13:00:00',
                        'duration_minutes' => 60,
                    ]);
                }
            }
        }
    }
}
