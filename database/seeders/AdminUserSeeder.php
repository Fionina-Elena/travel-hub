<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::create([
            'name' => 'Администратор',
            'email' => config('admin.email', 'admin@example.com'),
            'password' => bcrypt(config('admin.password', 'admin123')),
        ]);
    }
}
