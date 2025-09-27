<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CorrectionRequest;
use App\Models\Attendance;
use App\Models\User;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CorrectionRequest>
 */
class CorrectionRequestFactory extends Factory
{
    protected $model = CorrectionRequest::class;

    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'attendance_id' => Attendance::factory(),
            'field'         => 'clock_in',
            'before_value'  => null,
            'after_value'   => '09:00:00',
            'reason'        => $this->faker->sentence(),
            'requested_at'  => now(),
            'status'        => CorrectionRequest::STATUS_PENDING,
            'approver_id'   => null,
        ];
    }
}
