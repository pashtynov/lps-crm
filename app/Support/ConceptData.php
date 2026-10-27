<?php

namespace App\Support;

/**
 * Примерные данные для концепта следующих этапов по иерархии макета 2023 года:
 * Объект (ЖК) → Дом → Подъезд → Квартиры и МОП → Квартира с помещениями.
 * В базу ничего не пишется: всё считается из описания ниже, детерминированно.
 */
final class ConceptData
{
    public const OBJECT = 'ЖК «Новая Высота»';

    public const HEIGHT = 2.7;

    /** Типовые планировки: помещения [название, длина, ширина, площадь проёмов] — площади как в замере */
    public const LAYOUTS = [
        '1К-38' => [['Комната', 4.3, 4.0, 3.4], ['Кухня', 3.5, 2.6, 3.2], ['Прихожая', 3.0, 1.8, 4.8], ['Санузел', 2.0, 1.8, 1.6]],
        '2К-54' => [['Комната 1', 4.2, 3.9, 3.4], ['Комната 2', 3.6, 3.2, 3.2], ['Кухня', 3.4, 2.9, 3.2], ['Прихожая', 4.2, 1.6, 6.4], ['Ванная', 2.0, 1.7, 1.6], ['Туалет', 1.5, 0.9, 1.6]],
        '3К-72' => [['Комната 1', 4.5, 4.0, 3.4], ['Комната 2', 3.9, 3.3, 3.2], ['Комната 3', 3.6, 3.0, 3.2], ['Кухня', 3.6, 3.1, 3.2], ['Прихожая', 5.0, 1.7, 8.0], ['Ванная', 2.2, 1.7, 1.6], ['Туалет', 1.5, 0.9, 1.6]],
    ];

    /** МОП подъезда: на каждом этаже и отдельные помещения */
    private const MOP_FLOOR = [['Лифтовой холл и коридор', 6.0, 4.2, 8.0], ['Лестничная клетка', 5.6, 2.8, 2.0]];
    private const MOP_EXTRA = [['Тамбур и вестибюль', 1, 6.0, 4.0, 6.0], ['Техподполье', 0, 12.0, 6.0, 4.0], ['Выход на кровлю', 99, 3.0, 2.5, 1.6]];

    /** Виды работ по порядку; ставка — сдельная оплата за м², цена — для «выполнено на ₽» */
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
        5 => ['name' => 'Бригада 5', 'lead' => 'бригадир Олег', 'people' => 4, 'work' => 'putty'],
    ];

    /**
     * Дома: подъезды с планировками на этаже и фронт работ.
     * front: [подъезд => [работа => [последний сделанный этаж, этажи в работе, бригада]]]
     */
    public const HOUSES = [
        1 => ['name' => 'Дом 1', 'status' => 'Отделка завершается', 'floors' => 9,
            'sections' => [1 => ['1К-38', '2К-54', '2К-54', '1К-38'], 2 => ['2К-54', '3К-72', '2К-54'], 3 => ['1К-38', '2К-54', '2К-54', '1К-38']],
            'front' => [
                1 => ['plaster' => [9, [], 1], 'screed' => [9, [], 3], 'putty' => [7, [8, 9], 5], 'ceiling' => [5, [6], 4]],
                2 => ['plaster' => [9, [], 1], 'screed' => [9, [], 3], 'putty' => [6, [7], 5], 'ceiling' => [4, [5], 4]],
                3 => ['plaster' => [9, [], 2], 'screed' => [9, [], 3], 'putty' => [9, [], 5], 'ceiling' => [7, [8], 4]],
            ]],
        2 => ['name' => 'Дом 2', 'status' => 'В работе', 'floors' => 10,
            'sections' => [1 => ['1К-38', '2К-54', '3К-72', '2К-54', '1К-38', '2К-54'], 2 => ['2К-54', '3К-72', '3К-72', '2К-54']],
            'front' => [
                1 => ['plaster' => [7, [8, 9], 1], 'screed' => [5, [6], 3], 'putty' => [2, [3], 0], 'ceiling' => [1, [2], 4]],
                2 => ['plaster' => [4, [5, 6], 2], 'screed' => [2, [3], 3], 'putty' => [0, [], 0], 'ceiling' => [0, [], 0]],
            ]],
    ];

    private const PROBLEMS = [
        '2-1-8-4' => 'Нет доступа: собственник не отдал ключи',
        '2-2-5-1' => 'Мокрые стены после протечки кровли',
        '1-2-7-2' => 'Трещина в стяжке, нужен переделка участка',
    ];

    private static array $cache = [];

    /** Помещение по размерам: площадь пола и стен за вычетом проёмов */
    private static function room(string $name, float $a, float $b, float $openings): array
    {
        return ['name' => $name, 'a' => $a, 'b' => $b, 'floor_m2' => round($a * $b, 2),
            'walls' => round(2 * ($a + $b) * self::HEIGHT - $openings, 2), 'openings' => $openings];
    }

    private static function status(array $front, string $code, int $floor, int $lag = 0): array
    {
        [$done, $inWork, $brigade] = $front[$code];
        $done -= $lag;
        $inWork = $lag ? array_filter([$done + 1, $done + 2], fn ($f) => $f <= $front[$code][0]) : $inWork;
        $status = $floor <= $done ? ($floor <= $done - 2 ? 'accepted' : 'done') : (in_array($floor, $inWork, true) ? 'work' : 'todo');

        return ['status' => $status, 'brigade' => $status === 'todo' ? null : ($brigade ?: null)];
    }

    /** Все квартиры и МОП дома: [подъезд => ['flats' => [этаж => [кв…]], 'mop' => [...]]] */
    public static function house(int $house): array
    {
        if (isset(self::$cache[$house])) {
            return self::$cache[$house];
        }
        $h = self::HOUSES[$house];
        $out = [];
        $number = 1;
        foreach ($h['sections'] as $section => $types) {
            $front = $h['front'][$section];
            for ($floor = 1; $floor <= $h['floors']; $floor++) {
                foreach ($types as $i => $type) {
                    $rooms = array_map(fn ($r) => self::room(...$r), self::LAYOUTS[$type]);
                    $flat = ['n' => $number++, 'house' => $house, 'section' => $section, 'floor' => $floor, 'type' => $type, 'kind' => 'flat',
                        'rooms' => $rooms, 'walls' => round(array_sum(array_column($rooms, 'walls')), 1),
                        'floor_m2' => round(array_sum(array_column($rooms, 'floor_m2')), 1),
                        'measured' => ! ($house === 2 && $section === 2 && $floor === 10), 'works' => []];
                    foreach (array_keys(self::WORKS) as $code) {
                        $flat['works'][$code] = self::status($front, $code, $floor);
                        // на этаже в работе половина квартир уже готова
                        if ($flat['works'][$code]['status'] === 'work' && $i < intdiv(count($types), 2) && $floor === min($front[$code][1] ?: [0])) {
                            $flat['works'][$code]['status'] = 'done';
                        }
                    }
                    $flat['problem'] = self::PROBLEMS["$house-$section-$floor-$i"] ?? null;
                    if ($flat['problem'] && $flat['works']['plaster']['status'] !== 'todo' && $house === 2) {
                        $flat['works']['plaster']['status'] = 'work';
                    }
                    $out[$section]['flats'][$floor][] = $flat;
                }
            }
            krsort($out[$section]['flats']);

            // МОП: отделка идёт с отставанием на два этажа от квартир
            $mop = [];
            foreach (self::MOP_EXTRA as [$name, $floor, $a, $b, $op]) {
                $floor = $floor === 99 ? $h['floors'] : $floor;
                $mop[] = self::mopItem($name, $floor, self::room($name, $a, $b, $op), $front, $house, $section);
            }
            for ($floor = 1; $floor <= $h['floors']; $floor++) {
                foreach (self::MOP_FLOOR as [$name, $a, $b, $op]) {
                    $mop[] = self::mopItem("$name, эт. $floor", $floor, self::room($name, $a, $b, $op), $front, $house, $section);
                }
            }
            usort($mop, fn ($x, $y) => $x['floor'] <=> $y['floor']);
            $out[$section]['mop'] = $mop;
        }

        return self::$cache[$house] = $out;
    }

    private static function mopItem(string $name, int $floor, array $room, array $front, int $house, int $section): array
    {
        $works = [];
        foreach (array_keys(self::WORKS) as $code) {
            $works[$code] = $code === 'ceiling' ? ['status' => 'todo', 'brigade' => null] : self::status($front, $code, max($floor, 1), 2);
        }

        return ['name' => $name, 'floor' => $floor, 'house' => $house, 'section' => $section, 'kind' => 'mop',
            'walls' => $room['walls'], 'floor_m2' => $room['floor_m2'], 'works' => $works];
    }

    /** Плоский список квартир и МОП в области: весь объект, дом или подъезд */
    public static function units(?int $house = null, ?int $section = null, bool $withMop = true): array
    {
        $units = [];
        foreach ($house ? [$house] : array_keys(self::HOUSES) as $hn) {
            foreach (self::house($hn) as $sn => $s) {
                if ($section && $sn !== $section) {
                    continue;
                }
                foreach ($s['flats'] as $flats) {
                    array_push($units, ...$flats);
                }
                if ($withMop) {
                    array_push($units, ...$s['mop']);
                }
            }
        }

        return $units;
    }

    public static function flat(int $house, int $n): ?array
    {
        foreach (self::units($house, null, false) as $f) {
            if ($f['n'] === $n) {
                return $f;
            }
        }

        return null;
    }

    /** Прогресс по видам работ в м² и ₽ */
    public static function progress(?int $house = null, ?int $section = null): array
    {
        $res = [];
        foreach (self::WORKS as $code => $w) {
            $res[$code] = ['name' => $w['name'], 'color' => $w['color'], 'total' => 0, 'done' => 0, 'work' => 0];
        }
        foreach (self::units($house, $section) as $u) {
            foreach ($u['works'] as $code => $w) {
                $m2 = $u[self::WORKS[$code]['basis']];
                $res[$code]['total'] += $m2;
                if (in_array($w['status'], ['done', 'accepted'], true)) {
                    $res[$code]['done'] += $m2;
                } elseif ($w['status'] === 'work') {
                    $res[$code]['work'] += $m2;
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

    /** Общий процент готовности области: сделанные ₽ / все ₽ по смете */
    public static function overall(?int $house = null, ?int $section = null): int
    {
        $p = self::progress($house, $section);
        $total = array_sum(array_column($p, 'money_total'));

        return $total ? (int) round(array_sum(array_column($p, 'money_done')) / $total * 100) : 0;
    }

    /** Сводка по количеству: квартир, МОП, помещений, площадь */
    public static function counts(?int $house = null, ?int $section = null): array
    {
        $flats = self::units($house, $section, false);
        $all = self::units($house, $section);

        return [
            'flats' => count($flats),
            'mop' => count($all) - count($flats),
            'rooms' => array_sum(array_map(fn ($f) => count($f['rooms']), $flats)),
            'floor_m2' => array_sum(array_column($all, 'floor_m2')),
            'walls' => array_sum(array_column($all, 'walls')),
            'problems' => count(array_filter($flats, fn ($f) => $f['problem'])),
        ];
    }

    /** График: план по неделям (неделя 1 = 6 окт) и факт; отставание — сравнение доли на сегодня (середина 4-й недели) */
    public static function schedule(): array
    {
        $plan = [
            [1, 1, 'putty', 1, 5], [1, 1, 'ceiling', 1, 7], [1, 2, 'putty', 1, 5], [1, 2, 'ceiling', 2, 8], [1, 3, 'ceiling', 1, 6],
            [2, 1, 'plaster', 1, 6], [2, 2, 'plaster', 2, 7], [2, 1, 'screed', 2, 7], [2, 2, 'screed', 3, 8],
            [2, 1, 'putty', 3, 9], [2, 2, 'putty', 5, 10], [2, 1, 'ceiling', 4, 10], [2, 2, 'ceiling', 6, 11],
        ];
        $today = 4.4;
        $rows = [];
        foreach ($plan as [$house, $section, $work, $from, $to]) {
            $total = $done = 0;
            foreach (self::units($house, $section) as $u) {
                $m2 = $u[self::WORKS[$work]['basis']];
                $total += $m2;
                $done += in_array($u['works'][$work]['status'], ['done', 'accepted'], true) ? $m2 : 0;
            }
            $len = $to - $from + 1;
            $planShare = max(0, min(1, ($today - $from + 1) / $len));
            $fact = $total ? $done / $total : 0;
            $rows[] = ['house' => $house, 'section' => $section, 'work' => $work, 'from' => $from, 'to' => $to,
                'fact' => $fact, 'plan_share' => $planShare, 'lag_days' => (int) round(($planShare - $fact) * $len * 7),
                'name' => self::WORKS[$work]['name'], 'color' => self::WORKS[$work]['color']];
        }

        return ['rows' => $rows, 'weeks' => 11, 'today' => $today, 'start' => '2026-10-06'];
    }

    /** Бригады: где работают, сколько м² сделали, сколько начислено сдельно */
    public static function brigades(): array
    {
        $res = [];
        foreach (self::BRIGADES as $id => $b) {
            $res[$id] = $b + ['done' => 0, 'work_floors' => [], 'flats_work' => 0];
        }
        foreach (self::units() as $u) {
            foreach ($u['works'] as $code => $w) {
                if (! $w['brigade']) {
                    continue;
                }
                $m2 = $u[self::WORKS[$code]['basis']];
                if (in_array($w['status'], ['done', 'accepted'], true)) {
                    $res[$w['brigade']]['done'] += $m2;
                } elseif ($u['kind'] === 'flat') {
                    $res[$w['brigade']]['work_floors']["д.{$u['house']} п.{$u['section']} эт.{$u['floor']}"] = true;
                    $res[$w['brigade']]['flats_work']++;
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
