<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_has_many_attendances()
    {
        $user = User::factory()->create();
        Attendance::factory()->count(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->attendances);
        $this->assertInstanceOf(Attendance::class, $user->attendances->first());
    }
}
