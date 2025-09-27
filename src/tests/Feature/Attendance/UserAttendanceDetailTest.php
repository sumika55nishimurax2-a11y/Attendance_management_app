<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class UserAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $attendance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['name' => 'テスト太郎']);
        $this->user->markEmailAsVerified();

        $this->attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in'  => now()->subHours(8)->format('H:i:s'),
            'clock_out' => now()->format('H:i:s'),
        ]);
    }

    /** @test */
    public function name_is_logged_in_user_name()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->get(route('attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
    }

    /** @test */
    public function date_is_selected_date()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->get(route('attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y-m-d'));
    }

    /** @test */
    public function clock_in_and_out_times_match_records()
    {
        $attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->actingAs($this->user, 'web');

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($attendance->clock_in_formatted);
        $response->assertSee($attendance->clock_out_formatted);
    }

    /** @test */
    public function break_times_match_records()
    {
        $this->actingAs($this->user, 'web');

        $break = BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'break_start'   => '12:00:00',
            'break_end'     => '13:00:00',
        ]);

        $response = $this->get(route('attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee(substr($break->break_start, 0, 5)); // "12:00"
        $response->assertSee(substr($break->break_end, 0, 5));   // "13:00"
    }
}
