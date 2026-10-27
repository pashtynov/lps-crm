<?php

namespace App\Support;

use App\Calc\Catalog;
use App\Calc\Dec;
use App\Calc\ObjectEstimate;
use App\Models\CrmObject;

/**
 * Цифры для шапки объекта из макета (sPageInfo). Прогресса работ на этапе 1 нет, поэтому
 * кольцо — доля замеренных помещений, полосы — доля вида работ в смете, а не выдуманная готовность.
 */
final class ObjectSummary
{
    public const COLORS = ['#467CF4', '#1DBA6F', '#F4B13E', '#9A52DF', '#E94410', '#8B9298'];

    public static function for(CrmObject $object, ?array $estimate = null): array
    {
        $object->loadMissing('rooms.works', 'estimates');
        $estimate ??= ObjectEstimate::calculate($object);
        $rooms = $object->rooms;

        $areas = ['floor' => '0', 'walls' => '0'];
        $measured = 0;
        foreach ($rooms as $r) {
            $g = $r->geometry();
            $areas['floor'] = Dec::add($areas['floor'], $g['floor']);
            $areas['walls'] = Dec::add($areas['walls'], $g['walls']);
            if ((float) $g['floor'] > 0 && (float) $g['walls'] > 0) {
                $measured++;
            }
        }

        // доля вида работ (группы) в итоге сметы: работа + материалы
        $groups = [];
        foreach ($estimate['sections'] as $s) {
            foreach ($s['lines'] as $l) {
                $key = $l['group'] ?: $l['name'];
                $groups[$key] = ($groups[$key] ?? 0) + (float) $l['work_sum'] + (float) $l['materials_sum'];
            }
        }
        arsort($groups);
        $total = (float) $estimate['total'] ?: 1;
        $shares = [];
        foreach (array_slice($groups, 0, 6, true) as $name => $sum) {
            $shares[] = ['name' => $name, 'percent' => (int) round($sum / $total * 100), 'priced' => $sum > 0, 'color' => self::COLORS[count($shares)]];
        }

        return [
            'estimate' => $estimate,
            'rooms' => $rooms->count(),
            'lines' => $object->lines()->count(),
            'measured' => $measured,
            'measured_percent' => $rooms->count() ? (int) round($measured / $rooms->count() * 100) : 0,
            'buildings' => $rooms->pluck('building')->filter()->unique()->count(),
            'entrances' => $rooms->map(fn ($r) => $r->building . '/' . $r->entrance)->filter(fn ($v) => $v !== '/')->unique()->count(),
            'apartments' => $rooms->map(fn ($r) => $r->building . '/' . $r->entrance . '/' . $r->apartment)->filter(fn ($v) => ! str_ends_with($v, '/'))->unique()->count(),
            'floor' => $areas['floor'],
            'walls' => $areas['walls'],
            'variants' => $object->estimates->count(),
            'shares' => $shares,
            'with_works' => $rooms->filter(fn ($r) => $r->works->isNotEmpty())->count(),
            'surfaces' => Catalog::SURFACES,
        ];
    }
}
