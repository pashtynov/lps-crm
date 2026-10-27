<?php

namespace App\Console\Commands;

use App\Models\CrmObject;
use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;

/** Вернуть демо-объекты в исходное состояние после того, как клиент в них что-то поменял */
class ResetDemo extends Command
{
    protected $signature = 'crm:demo-reset {--force : без вопроса}';

    protected $description = 'Пересоздать демо-объекты (Бетанкура и демо-квартира); остальные объекты не трогаются';

    public function handle(): int
    {
        $names = DemoSeeder::NAMES;
        if (! $this->option('force') && ! $this->confirm('Пересоздать демо-объекты: ' . implode(', ', $names) . '?', true)) {
            return self::SUCCESS;
        }
        $deleted = CrmObject::whereIn('name', $names)->get()->each->delete()->count();
        $this->call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);
        $this->info("Удалено демо-объектов: {$deleted}, созданы заново");

        return self::SUCCESS;
    }
}
