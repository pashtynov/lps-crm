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

    /**
     * Виды работ, которые можно выбирать: есть цена работы и цены всех материалов.
     * Без цен позиция ушла бы в смету по нулю — до получения цен клиента прячем её из списков.
     */
    public static function activeWorkTypes(): array
    {
        $materials = self::materials();

        return array_filter(self::workTypes(), fn ($wt) => $wt['price'] !== null
            && collect($wt['materials'])->every(fn ($m) => ($materials[$m['material']]['price'] ?? null) !== null));
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
