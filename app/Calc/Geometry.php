<?php

namespace App\Calc;

/**
 * Площади и длины помещения по замеру — docs/crm-formuly.md, разделы 1–2.
 *
 * Замер: shape = rect (a, b) | l (стороны A–F, считаем по A, F, C, D) | manual (floor, perimeter);
 * height; openings — [type ОК|ДВ|ПР, width, height, wall (толщина стены), threshold (есть порог)].
 * Все размеры в метрах.
 */
final class Geometry
{
    /** Базы, от которых считается количество работы или материала */
    public const BASES = [
        'floor' => 'площадь пола',
        'floor_threshold' => 'пол + порог',
        'floor_opening' => 'пол + проём (где нет порога)',
        'walls' => 'стены за вычетом проёмов',
        'walls_gross' => 'стены без вычета проёмов',
        'ceiling' => 'потолок',
        'perimeter' => 'периметр',
        'perimeter_net' => 'периметр без дверей и проёмов',
    ];

    public static function measure(array $m): array
    {
        $shape = $m['shape'] ?? 'rect';
        $h = Dec::of($m['height'] ?? 0);

        if ($shape === 'l') {
            // Г-образная комната: габарит A × F минус вырез C × D; B = F − D, E = A − C
            [$a, $f, $c, $d] = array_map(fn ($k) => Dec::of($m[$k] ?? 0), ['a', 'f', 'c', 'd']);
            $floor = Dec::sub(Dec::mul($a, $f), Dec::mul($c, $d));
            $perimeter = Dec::mul('2', Dec::add($a, $f));
        } elseif ($shape === 'manual') {
            $floor = Dec::of($m['floor'] ?? 0);
            $perimeter = Dec::of($m['perimeter'] ?? 0);
        } else {
            $a = Dec::of($m['a'] ?? 0);
            $b = Dec::of($m['b'] ?? 0);
            $floor = Dec::mul($a, $b);
            $perimeter = Dec::mul('2', Dec::add($a, $b));
        }

        $openingsArea = '0';
        $doorWidths = '0';      // ДВ + ПР — то, что вычитается из периметра
        $threshold = '0';       // Sпорога = (ДВ + ПР) × толщина стены — стяжка идёт и под проём
        $openingFloor = '0';    // Sпроёма для чистовых покрытий — только где порога нет
        foreach ($m['openings'] ?? [] as $o) {
            $w = Dec::of($o['width'] ?? 0);
            $openingsArea = Dec::add($openingsArea, Dec::mul($w, Dec::of($o['height'] ?? 0)));
            if (($o['type'] ?? '') === 'ОК') {
                continue;
            }
            $doorWidths = Dec::add($doorWidths, $w);
            $sill = Dec::mul($w, Dec::of($o['wall'] ?? 0));
            $threshold = Dec::add($threshold, $sill);
            if (empty($o['threshold'])) {
                $openingFloor = Dec::add($openingFloor, $sill);
            }
        }

        $wallsGross = Dec::mul($perimeter, $h);

        return [
            'floor' => $floor,
            'floor_threshold' => Dec::add($floor, $threshold),
            'floor_opening' => Dec::add($floor, $openingFloor),
            'walls' => Dec::max0(Dec::sub($wallsGross, $openingsArea)),
            'walls_gross' => $wallsGross,
            'ceiling' => $floor,
            'perimeter' => $perimeter,
            'perimeter_net' => Dec::max0(Dec::sub($perimeter, $doorWidths)),
            'openings_area' => $openingsArea,
        ];
    }
}
