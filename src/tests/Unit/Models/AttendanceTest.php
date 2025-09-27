<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function attendance_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $attendance->user);
        $this->assertEquals($user->id, $attendance->user->id);
    }

    /** @test */
    public function attendance_has_many_breaks()
    {
        $attendance = Attendance::factory()->create();
        $attendance->breaks()->create([
            'break_start' => '12:00:00',
            'break_end'   => '12:30:00',
            'duration_minutes' => 30,
        ]);

        $this->assertCount(1, $attendance->breaks);
        $this->assertInstanceOf(BreakTime::class, $attendance->breaks->first());
    }

    /** @test */
    public function update_break_total_calculates_sum_of_breaks()
    {
        $attendance = Attendance::factory()->create();

        // 休憩1: 30分
        $attendance->breaks()->create([
            'break_start' => '12:00:00',
            'break_end'   => '12:30:00',
            'duration_minutes' => 30,
        ]);

        // 休憩2: 60分
        $attendance->breaks()->create([
            'break_start' => '15:00:00',
            'break_end'   => '16:00:00',
            'duration_minutes' => 60,
        ]);

        // 集計処理
        $attendance->updateBreakTotal();

        $this->assertEquals(90, $attendance->fresh()->break_time);
    }
}
