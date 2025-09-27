<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class UserAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->markEmailAsVerified();
    }

    /** @test */
    public function user_can_view_all_own_attendance_records()
    {
        $attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:15:00',
            'clock_out' => '18:15:00',
            'break_time' => 60,
        ]);

        $this->actingAs($this->user, 'web');

        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);

            $response->assertSee($attendance->clock_in_formatted);
            $response->assertSee($attendance->clock_out_formatted);
            $response->assertSee($attendance->break_formatted);

    }

    /** @test */
    public function current_month_is_displayed_by_default()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y/m')); // 例: 2025/09
    }

    /** @test */
    public function previous_month_button_displays_previous_month_data()
    {
        $lastMonth = Carbon::now()->subMonth();

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $lastMonth->copy()->startOfMonth()->toDateString(),
        ]);

        $this->actingAs($this->user, 'web');

        $response = $this->get(route('attendance.list', [
            'month' => $lastMonth->format('Y-m'),
        ]));

        $response->assertStatus(200);
        $response->assertSee($lastMonth->format('Y/m'));
    }

    /** @test */
    public function next_month_button_displays_next_month_data()
    {
        $nextMonth = Carbon::now()->addMonth();

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $nextMonth->copy()->startOfMonth()->toDateString(),
        ]);

        $this->actingAs($this->user, 'web');

        $response = $this->get(route('attendance.list', [
            'month' => $nextMonth->format('Y-m'),
        ]));

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('Y/m'));
    }

    /** @test */
    public function detail_button_redirects_to_attendance_detail_page()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
        ]);

        $this->actingAs($this->user, 'web');

        $response = $this->get(route('attendance.list'));

        $response->assertSee(route('attendance.detail', ['id' => $attendance->id]));
    }
}
