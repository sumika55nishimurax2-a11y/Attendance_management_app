<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function verification_mail_is_sent_after_registration()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'verifytest@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 登録後は verification.notice にリダイレクト
        $response->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'verifytest@example.com')->first();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test */
    public function user_can_view_verification_notice_page()
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        $response = $this->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertViewIs('staff.auth.verify-email');
    }

    /** @test */
    public function user_can_verify_email_and_is_redirected_to_attendance()
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        // 認証リンク生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        $response->assertRedirect(route('attendance'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function user_can_resend_verification_email()
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user, 'web');

        $response = $this->post(route('verification.send'));

        $response->assertRedirect(); // リダイレクトバック
        $response->assertSessionHas('message', 'Verification link sent!');

        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);
    }
}
