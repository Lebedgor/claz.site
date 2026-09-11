<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@claz.site'],
            ['name' => 'Admin', 'password' => 'claz-admin-2026'],
        );

        $this->call([
            CriteriaSeeder::class,
        ]);
    }
}
