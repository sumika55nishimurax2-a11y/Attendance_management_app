<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_is_required_on_login()
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function password_is_required_on_login()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function login_fails_with_invalid_credentials()
    {
        // 管理者ユーザーを作成
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // 間違ったパスワードでログイン
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest(); // ログインしていないことを確認
    }

    /** @test */
    public function admin_can_login_with_valid_credentials()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.attendance.list'));
        $this->assertAuthenticatedAs($admin);
    }
}
