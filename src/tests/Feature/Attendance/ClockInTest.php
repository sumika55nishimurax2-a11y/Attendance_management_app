<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function clock_in_button_switches_to_status_working()
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified();
        $this->actingAs($user, 'web');

        // 出勤前: 出勤ボタンがある
        $response = $this->get(route('attendance'));
        $response->assertSee('出勤');

        // 出勤処理
        $this->post(route('attendance.start'));

        // 出勤後: 出勤ボタンが消えてステータスが出勤中
        $response = $this->get(route('attendance'));
        $response->assertDontSee('<button type="submit" class="attendance-button">出勤</button>', false);
        $response->assertSee('出勤中');
    }

    /** @test */
    public function user_cannot_clock_in_more_than_once_per_day()
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified();

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in'  => now()->format('H:i:s'),
        ]);

        $this->actingAs($user, 'web');

        $response = $this->post(route('attendance.start'));

        // 出勤済みなので出勤処理は行われない
        $response->assertRedirect(route('attendance'));
        $this->assertDatabaseCount('attendances', 1);
    }

    /** @test */
    public function clock_in_time_is_displayed_in_attendance_list()
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified();

        $this->actingAs($user, 'web');
        $this->post(route('attendance.start'));

        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee(now()->format('H:i'));
    }
}
