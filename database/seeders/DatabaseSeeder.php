<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ModeratorSeeder::class,
        ]);

        $password = Str::password(24);
        $readerRole = Role::query()->where('name', Role::READER)->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => $password,
                'role_id' => $readerRole->id,
            ],
        );

        $this->command?->line("Аккаунт читателя: test@example.com | Пароль: {$password}");

        if (! Article::query()->exists()) {
            Article::factory(20)->create();
        }
    }
}
