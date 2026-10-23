<?php

namespace App\Calc;

use App\Models\CrmObject;

/**
 * Собирает смету по объекту: помещения с видами работ (площади из замера, суммируются по разделу
 * и виду работ), позиции с готовым объёмом и прочие позиции разделов. Порядок разделов — как
 * они впервые встречаются: сначала в позициях, потом в помещениях.
 */
final class ObjectEstimate
{
    public static function spec(CrmObject $object): array
    {
        $object->loadMissing(['rooms.works', 'lines', 'extras']);
        $workTypes = Catalog::workTypes();
        $sections = [];

        $section = function (string $name) use (&$sections) {
            $sections[$name] ??= ['name' => $name, 'lines' => [], 'extras' => []];
        };

        foreach ($object->lines as $l) {
            $section($l->section);
            $sections[$l->section]['lines'][] = ['work_type' => $l->work_type, 'qty' => $l->qty];
        }

        // помещения: суммируем все базы вида работ, чтобы округление шло на позиции
        $agg = [];
        foreach ($object->rooms as $room) {
            $g = $room->geometry();
            foreach ($room->works as $w) {
                if (! isset($workTypes[$w->work_type])) {
                    continue;
                }
                // разные толщины слоя — разные позиции сметы
                $key = $room->section . "\0" . $w->work_type . "\0" . json_encode($w->params ?? []);
                $agg[$key] ??= ['section' => $room->section, 'work_type' => $w->work_type, 'bases' => [], 'params' => $w->params ?? []];
                foreach (Geometry::BASES as $basis => $_) {
                    $agg[$key]['bases'][$basis] = Dec::add($agg[$key]['bases'][$basis] ?? '0', $g[$basis]);
                }
            }
        }
        foreach ($agg as $a) {
            $section($a['section']);
            $sections[$a['section']]['lines'][] = ['work_type' => $a['work_type'], 'bases' => $a['bases'], 'params' => $a['params']];
        }

        foreach ($object->extras as $e) {
            $section($e->section);
            $sections[$e->section]['extras'][] = ['code' => $e->extra, 'qty' => $e->qty];
        }

        return ['vat_rate' => (string) $object->vat_rate, 'sections' => array_values($sections)];
    }

    public static function calculate(CrmObject $object): array
    {
        return (new Estimator(Catalog::all()))->estimate(self::spec($object));
    }
}
