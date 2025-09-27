<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'user_id'   => User::factory(), // デフォルトでユーザーを紐づけ
            'work_date' => $this->faker->date('Y-m-d'),
            'clock_in'  => $this->faker->optional()->time('H:i:s'),
            'clock_out' => $this->faker->optional()->time('H:i:s'),
            'break_time' => 0,
            'note'      => $this->faker->optional()->sentence(),
        ];
    }
}
