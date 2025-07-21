<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = 'secret';
        $users = [
            [
                'name' => 'First User',
                'email' => 'first@example.com',
            ],
            [
                'name' => 'Second User',
                'email' => 'second@example.com',
            ],
            [
                'name' => 'Third User',
                'email' => 'third@example.com',
            ],
        ];

        foreach ($users as $attributes) {
            $user = User::factory()
                ->create([...$attributes, 'password' => $password]);

            Task::factory()
                ->user($user)
                ->count(rand(5, 10))
                ->create();
        }
    }
}
