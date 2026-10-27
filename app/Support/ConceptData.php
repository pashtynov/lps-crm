<?php

namespace App\Support;

/**
 * Примерные данные для концепта следующих этапов (шахматка, график, бригады, «Сегодня» прораба).
 * В базу ничего не пишется: всё считается из одного описания объекта, детерминированно.
 */
final class ConceptData
{
    public const OBJECT = 'ЖК «Новая Высота», дом 2';

    /** Типовые планировки: площадь стен и пола, м² (штукатурка — стены, стяжка и потолки — пол) */
    public const LAYOUTS = [
        '1К-38' => ['walls' => 74.0, 'floor_m2' => 34.5, 'rooms' => 4],
        '2К-54' => ['walls' => 104.0, 'floor_m2' => 49.8, 'rooms' => 6],
        '3К-72' => ['walls' => 136.0, 'floor_m2' => 66.2, 'rooms' => 8],
    ];

    /** Виды работ в порядке выполнения; ставка — сдельная оплата бригаде за м², цена — для «выполнено на ₽» */
    public const WORKS = [
        'plaster' => ['name' => 'Штукатурка', 'basis' => 'walls', 'rate' => 180, 'price' => 455, 'color' => '#F4B13E'],
        'screed' => ['name' => 'Стяжка', 'basis' => 'floor_m2', 'rate' => 150, 'price' => 520, 'color' => '#467CF4'],
        'putty' => ['name' => 'Шпатлёвка', 'basis' => 'walls', 'rate' => 120, 'price' => 290, 'color' => '#9A52DF'],
        'ceiling' => ['name' => 'Натяжные потолки', 'basis' => 'floor_m2', 'rate' => 50, 'price' => 650, 'color' => '#1DBA6F'],
    ];

    public const BRIGADES = [
        1 => ['name' => 'Бригада 1', 'lead' => 'бригадир Сергей', 'people' => 5, 'work' => 'plaster'],
        2 => ['name' => 'Бригада 2', 'lead' => 'бригадир Андрей', 'people' => 4, 'work' => 'plaster'],
        3 => ['name' => 'Бригада 3', 'lead' => 'бригадир Михаил', 'people' => 3, 'work' => 'screed'],
        4 => ['name' => 'Бригада 4 «Тандем»', 'lead' => 'бригадир Игорь', 'people' => 2, 'work' => 'ceiling'],
    ];

    public const FLOORS = 10;

    /**
     * Где сейчас фронт каждой работы: этажи ниже — сделано, фронт — в работе, выше — не начато.
     * [подъезд => [работа => [последний сделанный этаж, этажи в работе, бригада]]]
     */
    private const FRONT = [
        1 => ['plaster' => [7, [8, 9], 1], 'screed' => [5, [6], 3], 'putty' => [2, [3], 0], 'ceiling' => [1, [2], 4]],
        2 => ['plaster' => [4, [5, 6], 2], 'screed' => [2, [3], 3], 'putty' => [0, [], 0], 'ceiling' => [0, [], 0]],
    ];

    private const SECTION_FLATS = [1 => ['1К-38', '2К-54', '3К-72', '2К-54', '1К-38', '2К-54'], 2 => ['2К-54', '3К-72', '3К-72', '2К-54']];

    /** Шахматка: [подъезд => [этаж => [квартира…]]], сверху вниз */
    public static function chess(): array
    {
        $out = [];
        $number = 1;
        foreach (self::SECTION_FLATS as $section => $types) {
            for ($floor = 1; $floor <= self::FLOORS; $floor++) {
                foreach ($types as $i => $type) {
                    $flat = ['n' => $number++, 'section' => $section, 'floor' => $floor, 'type' => $type] + self::LAYOUTS[$type];
                    // замер: не замерены 10 этаж второго подъезда (нет доступа)
                    $flat['measured'] = ! ($section === 2 && $floor === 10);
                    $flat['works'] = [];
                    foreach (self::FRONT[$section] as $code => [$done, $inWork, $brigade]) {
                        $status = $floor <= $done ? 'done' : (in_array($floor, $inWork, true) ? 'work' : 'todo');
                        // на этаже в работе часть квартир уже готова
                        if ($status === 'work' && $i < intdiv(count($types), 2) && $floor === min($inWork)) {
                            $status = 'done';
                        }
                        if ($status === 'done' && $floor <= $done - 2) {
                            $status = 'accepted';
                        }
                        $flat['works'][$code] = ['status' => $status, 'brigade' => $status === 'todo' ? null : ($brigade ?: null)];
                    }
                    $flat['problem'] = match (true) {
                        $section === 1 && $floor === 8 && $i === 4 => 'Нет доступа: собственник не отдал ключи',
                        $section === 2 && $floor === 5 && $i === 1 => 'Мокрые стены после протечки кровли',
                        default => null,
                    };
                    // проблемная квартира стоит: штукатурка не закрыта
                    if ($flat['problem'] && $flat['works']['plaster']['status'] !== 'todo') {
                        $flat['works']['plaster']['status'] = 'work';
                    }
                    $out[$section][$floor][] = $flat;
                }
            }
            krsort($out[$section]);
        }

        return $out;
    }

    /** Прогресс по видам работ: м² сделано / всего, ₽ выполнено */
    public static function progress(): array
    {
        $res = [];
        foreach (self::WORKS as $code => $w) {
            $res[$code] = ['name' => $w['name'], 'color' => $w['color'], 'total' => 0, 'done' => 0, 'work' => 0];
        }
        foreach (self::chess() as $floors) {
            foreach ($floors as $flats) {
                foreach ($flats as $f) {
                    foreach ($f['works'] as $code => $w) {
                        $m2 = $f[self::WORKS[$code]['basis']];
                        $res[$code]['total'] += $m2;
                        if (in_array($w['status'], ['done', 'accepted'], true)) {
                            $res[$code]['done'] += $m2;
                        } elseif ($w['status'] === 'work') {
                            $res[$code]['work'] += $m2;
                        }
                    }
                }
            }
        }
        foreach ($res as $code => &$r) {
            $r['percent'] = $r['total'] ? (int) round($r['done'] / $r['total'] * 100) : 0;
            $r['money_done'] = $r['done'] * self::WORKS[$code]['price'];
            $r['money_total'] = $r['total'] * self::WORKS[$code]['price'];
        }

        return $res;
    }

    /**
     * График: план по неделям (неделя 1 = 6 окт) и факт. Отставание в днях — из сравнения
     * плановой и фактической доли сделанного на сегодня (неделя 4).
     */
    public static function schedule(): array
    {
        $plan = [
            ['work' => 'plaster', 'section' => 1, 'from' => 1, 'to' => 6],
            ['work' => 'plaster', 'section' => 2, 'from' => 2, 'to' => 7],
            ['work' => 'screed', 'section' => 1, 'from' => 2, 'to' => 7],
            ['work' => 'screed', 'section' => 2, 'from' => 3, 'to' => 8],
            ['work' => 'putty', 'section' => 1, 'from' => 3, 'to' => 9],
            ['work' => 'putty', 'section' => 2, 'from' => 5, 'to' => 10],
            ['work' => 'ceiling', 'section' => 1, 'from' => 4, 'to' => 10],
            ['work' => 'ceiling', 'section' => 2, 'from' => 6, 'to' => 11],
        ];
        $today = 4.4; // середина 4-й недели
        $chess = self::chess();
        foreach ($plan as &$row) {
            $total = $done = 0;
            foreach ($chess[$row['section']] as $flats) {
                foreach ($flats as $f) {
                    $m2 = $f[self::WORKS[$row['work']]['basis']];
                    $total += $m2;
                    $done += in_array($f['works'][$row['work']]['status'], ['done', 'accepted'], true) ? $m2 : 0;
                }
            }
            $len = $row['to'] - $row['from'] + 1;
            $planShare = max(0, min(1, ($today - $row['from'] + 1) / $len));
            $row['fact'] = $total ? $done / $total : 0;
            $row['plan_share'] = $planShare;
            $row['lag_days'] = (int) round(($planShare - $row['fact']) * $len * 7);
            $row['name'] = self::WORKS[$row['work']]['name'];
            $row['color'] = self::WORKS[$row['work']]['color'];
        }

        return ['rows' => $plan, 'weeks' => 11, 'today' => $today, 'start' => '2026-10-06'];
    }

    /** Бригады: где работают, сколько м² сделали, сколько заработали по сдельной ставке */
    public static function brigades(): array
    {
        $res = [];
        foreach (self::BRIGADES as $id => $b) {
            $res[$id] = $b + ['done' => 0, 'work_floors' => [], 'flats_work' => 0];
        }
        foreach (self::chess() as $section => $floors) {
            foreach ($floors as $floor => $flats) {
                foreach ($flats as $f) {
                    foreach ($f['works'] as $code => $w) {
                        if (! $w['brigade']) {
                            continue;
                        }
                        $m2 = $f[self::WORKS[$code]['basis']];
                        if (in_array($w['status'], ['done', 'accepted'], true)) {
                            $res[$w['brigade']]['done'] += $m2;
                        } else {
                            $res[$w['brigade']]['work_floors']["п.{$section} эт.{$floor}"] = true;
                            $res[$w['brigade']]['flats_work']++;
                        }
                    }
                }
            }
        }
        foreach ($res as &$b) {
            $b['earned'] = $b['done'] * self::WORKS[$b['work']]['rate'];
            $b['where'] = implode(', ', array_keys($b['work_floors'])) ?: 'ждёт фронт работ';
            $b['work_name'] = self::WORKS[$b['work']]['name'];
            $b['color'] = self::WORKS[$b['work']]['color'];
        }

        return $res;
    }
}
