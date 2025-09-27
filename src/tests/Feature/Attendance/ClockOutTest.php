<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class ClockOutTest extends TestCase
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
            'clock_in'  => now()->subHours(8)->format('H:i:s'),
            'clock_out' => null,
        ]);
    }

    /** @test */
    public function clock_out_button_works_properly()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->post(route('attendance.finish'));

        $response->assertRedirect(route('attendance'));

        $this->assertDatabaseHas('attendances', [
            'id'        => $this->attendance->id,
            'user_id'   => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_out' => now()->format('H:i:s'),
        ]);
    }

    /** @test */
    public function clock_out_time_is_displayed_in_attendance_list()
    {
        $this->actingAs($this->user, 'web');

        // 出勤中 → 退勤処理
        $this->post(route('attendance.finish'));

        // 勤怠一覧を確認
        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee(now()->format('H:i')); // 退勤時刻が表示される
    }
}
