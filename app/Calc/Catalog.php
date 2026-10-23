<?php

namespace App\Calc;

/**
 * Справочник видов работ, материалов, норм и цен. На этапе 1 — файл database/data/catalog.json,
 * правим мы; редактирование клиентом с историей — этап 3.
 */
final class Catalog
{
    private static ?array $data = null;

    public static function all(): array
    {
        return self::$data ??= json_decode(
            file_get_contents(base_path('database/data/catalog.json')), true, flags: JSON_THROW_ON_ERROR
        );
    }

    public static function workTypes(): array
    {
        return array_column(self::all()['work_types'], null, 'code');
    }

    public static function materials(): array
    {
        return array_column(self::all()['materials'], null, 'code');
    }

    public static function extras(): array
    {
        return array_column(self::all()['extras'], null, 'code');
    }

    public const SURFACES = ['floor' => 'Пол', 'walls' => 'Стены', 'ceiling' => 'Потолок'];
}
