<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Carbon\Carbon;

class UserCorrectionRequestTest extends TestCase
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
            'work_date' => Carbon::yesterday()->toDateString(), // 過去日
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'note'      => '元の備考',
        ]);
    }

    /** @test */
    public function error_when_clock_in_time_is_after_clock_out_time()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->post(route('attendance.detail.update', $this->attendance->id), [
            'clock_in'  => '20:00',
            'clock_out' => '09:00',
            'note'      => 'テスト',
            'reason'    => 'テスト理由',
        ]);

        $response->assertSessionHasErrors(['clock_in']);
        $this->assertEquals(
            '出勤時間もしくは退勤時間が不適切な値です',
            session('errors')->first('clock_in')
        );
    }

    /** @test */
    public function error_when_reason_is_empty()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->post(route('attendance.detail.update', $this->attendance->id), [
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'note'      => 'テスト備考',
            'reason'    => '', // ← 申請理由を空にする
        ]);

        $response->assertSessionHasErrors(['reason']);
        $this->assertEquals(
            '備考を記入してください',
            session('errors')->first('reason')
        );
    }

    /** @test */
    public function correction_request_is_created_successfully()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->post(route('attendance.detail.update', $this->attendance->id), [
            'clock_in'  => '09:30',
            'clock_out' => '18:30',
            'note'      => '修正後備考',
            'reason'    => '通院のため',
            'breaks'    => [
                ['start' => '12:00', 'end' => '12:30'],
            ],
        ]);

        $response->assertRedirect(route('attendance.detail', ['id' => $this->attendance->id]));
        $this->assertDatabaseHas('correction_requests', [
            'attendance_id' => $this->attendance->id,
            'reason'        => '通院のため',
            'status'        => CorrectionRequest::STATUS_PENDING,
        ]);
    }

    /** @test */
    public function correction_request_contains_before_and_after_values()
    {
        $this->actingAs($this->user, 'web');

        $this->post(route('attendance.detail.update', $this->attendance->id), [
            'clock_in'  => '09:30',
            'clock_out' => '18:30',
            'note'      => '修正後備考',
            'reason'    => '通院のため',
        ]);

        $correction = CorrectionRequest::first();

        $before = json_decode($correction->before_value, true);
        $after  = $correction->after_value; // JSON文字列そのまま

        // before の検証
        $this->assertEquals('09:00', substr($before['clock_in'], 0, 5));
        $this->assertEquals('18:00', substr($before['clock_out'], 0, 5));
        $this->assertEquals('元の備考', $before['note']);

        // after の検証（UTCに変換されても "00:30" と "09:30" が含まれるはず）
        $this->assertStringContainsString('00:30', $after);
        $this->assertStringContainsString('09:30', $after);

        $this->assertEquals('通院のため', $correction->reason);
    }
}
