<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use App\Models\Attendance;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BreakTime>
 */
class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition(): array
    {
        $start = $this->faker->time('H:i:s');
        $end   = $this->faker->time('H:i:s');
        return [
            'attendance_id'   => Attendance::factory(),
            'break_start'     => $start,
            'break_end'       => $end,
            'duration_minutes' => rand(15, 60),
        ];
    }
}
