<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/** Завести пользователя (например, клиенту для демо). Пароль вводится скрыто и нигде не сохраняется открытым */
class CreateUser extends Command
{
    protected $signature = 'crm:user {email} {name}';

    protected $description = 'Создать пользователя CRM или сменить ему пароль';

    public function handle(): int
    {
        $password = $this->secret('Пароль (не меньше 8 символов)');
        if (mb_strlen((string) $password) < 8) {
            $this->error('Пароль короче 8 символов');
            return self::FAILURE;
        }
        if ($password !== $this->secret('Повторите пароль')) {
            $this->error('Пароли не совпали');
            return self::FAILURE;
        }

        $user = User::updateOrCreate(['email' => $this->argument('email')], [
            'name' => $this->argument('name'), 'password' => $password,
        ]);
        $this->info(($user->wasRecentlyCreated ? 'Создан' : 'Обновлён') . ": {$user->email}");

        return self::SUCCESS;
    }
}
