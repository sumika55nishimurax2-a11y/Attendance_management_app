<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $attendance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->markEmailAsVerified();
        $this->attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in'  => now()->subHour()->format('H:i:s'),
        ]);
    }

    /** @test */
    public function break_in_button_works_properly()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->post(route('attendance.break_start'));

        $response->assertRedirect(route('attendance'));

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $this->attendance->id,
        ]);
    }

    /** @test */
    public function user_can_take_multiple_breaks_in_a_day()
    {
        $this->actingAs($this->user, 'web');

        // 1回目
        $this->post(route('attendance.break_start'));
        $this->post(route('attendance.break_end'));

        // 2回目
        $this->post(route('attendance.break_start'));

        $this->assertDatabaseCount('break_times', 2);
    }

    /** @test */
    public function break_out_button_works_properly()
    {
        $this->actingAs($this->user, 'web');

        $this->post(route('attendance.break_start'));
        $response = $this->post(route('attendance.break_end'));

        $response->assertRedirect(route('attendance'));

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $this->attendance->id,
            'break_end'     => now()->format('H:i:s'),
        ]);
    }

    /** @test */
    public function user_can_resume_work_multiple_times()
    {
        $this->actingAs($this->user, 'web');

        // 休憩1回目
        $this->post(route('attendance.break_start'));
        $this->post(route('attendance.break_end'));

        // 休憩2回目
        $this->post(route('attendance.break_start'));
        $this->post(route('attendance.break_end'));

        $this->assertDatabaseCount('break_times', 2);
    }

    /** @test */
    public function break_time_is_displayed_in_attendance_list()
    {
        $this->actingAs($this->user, 'web');

        // DBに休憩を直接作る
        $this->attendance->breaks()->create([
            'break_start' => now()->subMinutes(30),
            'break_end'   => now(),
            'duration_minutes' => 30,
        ]);
        $this->attendance->updateBreakTotal();

        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('0:30');
    }
}
