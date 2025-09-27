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
    public function user_can_clock_in_successfully()
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified();

        $this->actingAs($user, 'web');

        $response = $this->post(route('attendance.start'));

        $response->assertRedirect(route('attendance'));

        $this->assertDatabaseHas('attendances', [
            'user_id'   => $user->id,
            'work_date' => now()->toDateString(),
        ]);
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
