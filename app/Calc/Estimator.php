<?php

namespace App\Calc;

/**
 * Смета по правилам сметы Бетанкура — docs/crm-formuly.md, раздел 5.
 *
 * Позиция работы: кол-во = сумма базы (площади) по помещениям, стоимость = ROUND(кол-во × цена, 2).
 * Материал: кол-во = CEILING(точное кол-во по формуле, шаг) — округляем на позиции, не на помещении.
 * Цены с НДС, НДС выделяется из итога: ВСЕГО / (1 + ставка) × ставка.
 *
 * $catalog — ['materials' => [...], 'work_types' => [...], 'extras' => [...]] в формате
 * database/data/catalog.json. $spec — ['vat_rate' => 0.22, 'sections' => [[name, lines, extras]]],
 * строка: ['work_type' => код, 'qty' => число] или ['work_type' => код, 'bases' => [база => число]].
 */
final class Estimator
{
    public const FORMULAS = [
        'area' => 'S × N',
        'area_layers' => 'S × N × K',
        'area_thickness' => 'S × t × N',
        'area_layers_thickness' => 'S × K × t × N',
        'volume' => 'S × t / 1000 × N',
        'length' => 'L × N',
        'count' => 'n × N',
    ];

    private array $materials;
    private array $workTypes;
    private array $extras;

    public function __construct(array $catalog)
    {
        $this->materials = array_column($catalog['materials'], null, 'code');
        $this->workTypes = array_column($catalog['work_types'], null, 'code');
        $this->extras = array_column($catalog['extras'] ?? [], null, 'code');
    }

    public function estimate(array $spec): array
    {
        $rate = Dec::of($spec['vat_rate'] ?? '0.22');
        $out = ['sections' => [], 'work' => '0', 'materials' => '0', 'area' => '0', 'warnings' => []];

        foreach ($spec['sections'] as $section) {
            $s = ['name' => $section['name'], 'lines' => [], 'extras' => [], 'work' => '0', 'materials' => '0'];

            foreach ($section['lines'] as $line) {
                $l = $this->line($line, $out['warnings']);
                $s['lines'][] = $l;
                $s['work'] = Dec::add($s['work'], $l['work_sum']);
                $s['materials'] = Dec::add($s['materials'], $l['materials_sum']);
                if ($l['unit'] === 'м2') {
                    $out['area'] = Dec::add($out['area'], $l['qty']);
                }
            }

            foreach ($section['extras'] ?? [] as $e) {
                $x = $this->extras[$e['code']];
                $qty = Dec::of($e['qty']);
                $sum = Dec::round2(Dec::mul($qty, Dec::of($x['price'])));
                $s['extras'][] = ['code' => $x['code'], 'name' => $x['name'], 'unit' => $x['unit'],
                    'qty' => $qty, 'price' => Dec::of($x['price']), 'sum' => $sum];
                $s['work'] = Dec::add($s['work'], $sum);
            }

            $out['sections'][] = $s;
            $out['work'] = Dec::add($out['work'], $s['work']);
            $out['materials'] = Dec::add($out['materials'], $s['materials']);
        }

        $out['total'] = Dec::add($out['work'], $out['materials']);
        $out['vat_rate'] = $rate;
        $out['vat'] = Dec::round2(Dec::mul(Dec::div($out['total'], Dec::add('1', $rate)), $rate));
        $out['warnings'] = array_values(array_unique($out['warnings']));

        return $out;
    }

    private function line(array $line, array &$warnings): array
    {
        $wt = $this->workTypes[$line['work_type']]
            ?? throw new \InvalidArgumentException("Нет вида работ {$line['work_type']}");

        $bases = array_map(fn ($v) => Dec::of($v), $line['bases'] ?? [$wt['basis'] => $line['qty'] ?? 0]);
        $qty = $bases[$wt['basis']] ?? '0';
        $params = ($line['params'] ?? []) + ($wt['params'] ?? []);
        $layers = Dec::of($params['layers'] ?? 1);
        $thickness = Dec::of($params['thickness'] ?? 0);

        $price = $wt['price'] === null ? null : Dec::of($wt['price']);
        if ($price === null) {
            $warnings[] = "Нет цены работы: {$wt['name']}";
        }

        $mats = [];
        $matSum = '0';
        foreach ($wt['materials'] as $m) {
            $mat = $this->materials[$m['material']];
            $basis = $m['basis'] ?? $wt['basis'];
            if (! isset($bases[$basis])) {
                $warnings[] = 'Нет данных замера для «' . ($m['label'] ?? $mat['name']) . '» (' . (Geometry::BASES[$basis] ?? $basis) . ') — задайте помещения, а не готовую площадь';
                continue;
            }
            $exact = $this->quantity($m['formula'], $bases[$basis], Dec::of($m['norm']), $layers, $thickness);
            $rounded = Dec::ceil($exact, Dec::of($m['step'] ?? 1));
            $mPrice = $mat['price'] === null ? null : Dec::of($mat['price']);
            $sum = $mPrice === null ? null : Dec::round2(Dec::mul($rounded, $mPrice));
            if ($mPrice === null) {
                $warnings[] = "Нет цены материала: {$mat['name']}";
            }
            $packs = isset($mat['pack']) ? Dec::ceil(Dec::div($rounded, Dec::of($mat['pack']['size'])), '1') : null;
            $mats[] = [
                'code' => $mat['code'], 'name' => $m['label'] ?? $mat['name'], 'unit' => $mat['unit'],
                'formula' => $m['formula'], 'norm' => Dec::of($m['norm']),
                // для Excel: кол-во = CEILING(объём позиции × factor; step), если база та же, что у работы
                'factor' => $this->quantity($m['formula'], '1', Dec::of($m['norm']), $layers, $thickness),
                'step' => Dec::of($m['step'] ?? 1), 'same_basis' => $basis === $wt['basis'],
                'exact' => $exact, 'qty' => $rounded, 'price' => $mPrice, 'sum' => $sum,
                'packs' => $packs, 'pack_name' => $mat['pack']['name'] ?? null,
            ];
            $matSum = Dec::add($matSum, $sum ?? '0');
        }

        return [
            'code' => $wt['code'], 'name' => $wt['name'], 'group' => $wt['group'] ?? '', 'unit' => $wt['unit'],
            'qty' => $qty, 'price' => $price,
            'work_sum' => $price === null ? '0' : Dec::round2(Dec::mul($qty, $price)),
            'materials' => $mats, 'materials_sum' => $matSum,
        ];
    }

    private function quantity(string $formula, string $s, string $n, string $k, string $t): string
    {
        return match ($formula) {
            'area', 'length', 'count' => Dec::mul($s, $n),
            'area_layers' => Dec::mul($s, $n, $k),
            'area_thickness' => Dec::mul($s, $t, $n),
            'area_layers_thickness' => Dec::mul($s, $k, $t, $n),
            'volume' => Dec::mul($s, Dec::div($t, '1000'), $n),
            default => throw new \InvalidArgumentException("Неизвестная формула $formula"),
        };
    }
}
