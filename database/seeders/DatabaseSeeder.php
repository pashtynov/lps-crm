<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Пароля в репозитории нет: первый пользователь — из .env сервера
        if (env('CRM_ADMIN_EMAIL') && env('CRM_ADMIN_PASSWORD')) {
            User::updateOrCreate(['email' => env('CRM_ADMIN_EMAIL')],
                ['name' => env('CRM_ADMIN_NAME', 'Администратор'), 'password' => env('CRM_ADMIN_PASSWORD')]);
        } else {
            $this->command?->warn('CRM_ADMIN_EMAIL / CRM_ADMIN_PASSWORD не заданы — пользователь не создан');
        }

        $this->call(DemoSeeder::class);
    }
}
