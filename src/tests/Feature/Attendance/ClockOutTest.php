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
    public function user_can_clock_out_successfully()
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified();
        $this->actingAs($user, 'web');

        // 出勤処理
        $this->post(route('attendance.start'));

        // 退勤前に「退勤」ボタンが見える
        $response = $this->get(route('attendance'));
        $response->assertSee('退勤');

        // 退勤処理
        $this->post(route('attendance.finish'));

        // 退勤後に「退勤」ボタンが消え、ステータスが「退勤済」
        $response = $this->get(route('attendance'));
        $response->assertStatus(200);
        $response->assertDontSee('<button type="submit" class="attendance-button">退勤</button>', false);
        $response->assertSee('退勤済');
    }
}
