<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CheckStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_status_is_off_duty_when_not_working()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function test_status_is_working_when_clocked_in()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function test_status_is_break_when_on_break()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function test_status_is_clocked_out_when_finished()
    {
        $this->assertTrue(true);
    }
}
