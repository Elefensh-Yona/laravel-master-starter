<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\SystemRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(SettingsSeeder::class);

        User::query()->updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
        ])->syncRoles([SystemRole::SUPER_ADMIN]);
    }
}
