<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $staff;
    private $attendance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $this->staff = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->staff->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'note' => '初期備考',
        ]);
    }

    /** @test */
    public function admin_can_view_selected_user_attendance_detail()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($this->attendance->clock_in_formatted);
        $response->assertSee($this->attendance->clock_out_formatted);
    }

    /** @test */
    public function error_when_clock_in_is_after_clock_out()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.attendance.update', ['id' => $this->attendance->id]), [
            'clock_in' => '20:00',
            'clock_out' => '09:00',
            'note' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors(['clock_in']);
    }

    /** @test */
    public function error_when_break_start_is_after_clock_out()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.attendance.update', ['id' => $this->attendance->id]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => 'テスト備考',
            'breaks' => [
                ['start' => '19:00', 'end' => null],
            ],
        ]);

        $response->assertSessionHasErrors(['breaks.0.start']);
    }

    /** @test */
    public function error_when_break_end_is_after_clock_out()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.attendance.update', ['id' => $this->attendance->id]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => 'テスト備考',
            'breaks' => [
                ['start' => '12:00', 'end' => '19:00'],
            ],
        ]);

        $response->assertSessionHasErrors(['breaks.0.end']);
    }

    /** @test */
    public function error_when_reason_is_empty()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.attendance.update', ['id' => $this->attendance->id]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => '',
        ]);

        $response->assertSessionHasErrors(['reason']);
    }
}
