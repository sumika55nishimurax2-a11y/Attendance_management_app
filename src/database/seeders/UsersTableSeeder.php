<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '山田太一(管理者)',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'registered_at' => now(),
                'email_verified_at' => now(),
            ]
        );
        $users = [
            ['name' => '佐藤太郎', 'email' => 'taro.sato@example.com'],
            ['name' => '鈴木花子', 'email' => 'hanako.suzuki@example.com'],
            ['name' => '田中一郎', 'email' => 'ichiro.tanaka@example.com'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'registered_at' => now(),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
