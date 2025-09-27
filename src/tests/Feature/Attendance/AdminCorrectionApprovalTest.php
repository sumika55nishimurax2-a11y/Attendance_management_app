<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Carbon\Carbon;

class AdminCorrectionApprovalTest extends TestCase
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
            'user_id'   => $this->staff->id,
            'work_date' => Carbon::yesterday()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'note'      => '元の備考',
        ]);
    }

    /** @test */
    public function pending_correction_requests_are_displayed()
    {
        CorrectionRequest::factory()->create([
            'attendance_id' => $this->attendance->id,
            'user_id'       => $this->staff->id,
            'reason'        => 'テスト理由',
            'status'        => CorrectionRequest::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.stamp_correction_request.list'));

        $response->assertStatus(200);
        $response->assertSee('テスト理由');
    }

    /** @test */
    public function approved_correction_requests_are_displayed()
    {
        CorrectionRequest::factory()->create([
            'attendance_id' => $this->attendance->id,
            'user_id'       => $this->staff->id,
            'reason'        => '承認済みテスト',
            'status'        => CorrectionRequest::STATUS_APPROVED,
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.stamp_correction_request.list'));

        $response->assertStatus(200);
        $response->assertSee('承認済みテスト');
    }

    /** @test */
    public function correction_request_detail_is_displayed()
    {
        $correction = CorrectionRequest::factory()->create([
            'attendance_id' => $this->attendance->id,
            'user_id'       => $this->staff->id,
            'reason'        => '詳細確認テスト',
            'status'        => CorrectionRequest::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.stamp_correction_request.show', $correction->id));

        $response->assertStatus(200);
        $response->assertSee('詳細確認テスト');
    }

    /** @test */
    public function admin_can_approve_correction_request_and_update_attendance()
    {
        $correctionIn = CorrectionRequest::factory()->create([
            'attendance_id' => $this->attendance->id,
            'user_id'       => $this->staff->id,
            'field'         => 'clock_in',
            'after_value'   => '10:00:00',
            'status'        => CorrectionRequest::STATUS_PENDING,
        ]);

        $correctionOut = CorrectionRequest::factory()->create([
            'attendance_id' => $this->attendance->id,
            'user_id'       => $this->staff->id,
            'field'         => 'clock_out',
            'after_value'   => '19:00:00',
            'status'        => CorrectionRequest::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.stamp_correction_request.approve', $correctionIn->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('correction_requests', [
            'id'     => $correctionIn->id,
            'status' => CorrectionRequest::STATUS_APPROVED,
        ]);
        $this->assertDatabaseHas('correction_requests', [
            'id'     => $correctionOut->id,
            'status' => CorrectionRequest::STATUS_APPROVED,
        ]);
        $this->assertDatabaseHas('attendances', [
            'id'        => $this->attendance->id,
            'clock_in'  => '10:00:00',
            'clock_out' => '19:00:00',
        ]);
    }
}
