<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::query()->updateOrCreate(
            ['name' => Role::MODERATOR],
            ['display_name' => 'Модератор'],
        );

        $readerRole = Role::query()->updateOrCreate(
            ['name' => Role::READER],
            ['display_name' => 'Читатель'],
        );

        User::query()->whereNull('role_id')->update(['role_id' => $readerRole->id]);
    }
}
