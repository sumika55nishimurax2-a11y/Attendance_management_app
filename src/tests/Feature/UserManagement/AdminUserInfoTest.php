<?php

namespace Tests\Feature\UserManagement;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminUserInfoTest extends TestCase
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
            'name' => 'テストユーザー',
            'email' => 'staff@example.com',
        ]);
    }

    /** @test */
    public function admin_can_view_all_users_info()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.staffs.index'));

        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
        $response->assertSee('staff@example.com');
    }

    /** @test */
    public function admin_can_view_selected_user_attendance()
    {
        Attendance::factory()->create([
            'user_id'   => $this->staff->id,
            'work_date' => now()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.staffs.attendance', [
            'user_id' => $this->staff->id,
            'year'    => now()->year,
            'month'   => now()->month,
        ]));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function previous_month_button_displays_previous_month_data()
    {
        $lastMonth = Carbon::now()->subMonth();

        Attendance::factory()->create([
            'user_id'   => $this->staff->id,
            'work_date' => $lastMonth->copy()->startOfMonth()->toDateString(),
            'clock_in'  => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.staffs.attendance', [
            'user_id' => $this->staff->id,
            'year'    => $lastMonth->year,
            'month'   => $lastMonth->month,
        ]));

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function next_month_button_displays_next_month_data()
    {
        $nextMonth = Carbon::now()->addMonth();

        Attendance::factory()->create([
            'user_id'   => $this->staff->id,
            'work_date' => $nextMonth->copy()->startOfMonth()->toDateString(),
            'clock_in'  => '08:30:00',
            'clock_out' => '17:30:00',
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.staffs.attendance', [
            'user_id' => $this->staff->id,
            'year'    => $nextMonth->year,
            'month'   => $nextMonth->month,
        ]));

        $response->assertStatus(200);
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }

    /** @test */
    public function admin_can_access_attendance_detail_page()
    {
        $attendance = Attendance::factory()->create([
            'user_id'   => $this->staff->id,
            'work_date' => now()->toDateString(),
            'clock_in'  => '09:15:00',
            'clock_out' => '18:15:00',
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('09:15');
        $response->assertSee('18:15');
    }
}
