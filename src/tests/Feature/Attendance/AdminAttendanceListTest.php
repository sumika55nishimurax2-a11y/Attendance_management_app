<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;


class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $staff;

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
    }

    /** @test */
    public function admin_can_view_all_users_attendance_of_selected_day()
    {
        $today = now()->toDateString();

        Attendance::factory()->create([
            'user_id' => $this->staff->id,
            'work_date' => $today,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.attendance.list', ['date' => $today]));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function current_day_is_displayed_by_default()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y-m-d'));
    }

    /** @test */
    public function previous_day_button_displays_previous_day_data()
    {
        $yesterday = Carbon::yesterday()->toDateString();

        Attendance::factory()->create([
            'user_id' => $this->staff->id,
            'work_date' => $yesterday,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.attendance.list', ['date' => $yesterday]));

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function next_day_button_displays_next_day_data()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        Attendance::factory()->create([
            'user_id' => $this->staff->id,
            'work_date' => $tomorrow,
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.attendance.list', ['date' => $tomorrow]));

        $response->assertStatus(200);
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }
}
