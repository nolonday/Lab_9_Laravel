<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ModeratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Str::password(24);
        $moderatorRole = Role::query()->where('name', Role::MODERATOR)->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => 'moderator@example.com'],
            [
                'name' => 'Модератор',
                'password' => $password,
                'role_id' => $moderatorRole->id,
            ],
        );

        $this->command?->line("Аккаунт модератора: moderator@example.com | Пароль: {$password}");
    }
}
