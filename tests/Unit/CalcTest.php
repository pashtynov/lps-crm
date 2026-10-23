<?php

use App\Calc\Estimator;
use App\Calc\Geometry;

function catalog(): array
{
    return json_decode(file_get_contents(__DIR__ . '/../../database/data/catalog.json'), true);
}

it('повторяет смету Бетанкура 20.05.2024 до копейки', function () {
    $fx = json_decode(file_get_contents(__DIR__ . '/../fixtures/betancura-2024-05-20.json'), true);
    $r = (new Estimator(catalog()))->estimate($fx);

    foreach ($fx['sections'] as $i => $s) {
        expect((float) $r['sections'][$i]['work'])->toBe((float) $s['expect']['work'])
            ->and((float) $r['sections'][$i]['materials'])->toBe((float) $s['expect']['materials']);
    }
    expect((float) $r['work'])->toBe(19565064.52)
        ->and((float) $r['materials'])->toBe(13155174.59)
        ->and((float) $r['total'])->toBe(32720239.11)
        ->and((float) $r['vat'])->toBe(5453373.19)
        ->and((float) $r['area'])->toBe(42928.86);
});

it('считает Г-образную комнату из примера crm-formuly.md', function () {
    $g = Geometry::measure([
        'shape' => 'l', 'a' => 4, 'f' => 5, 'c' => 1.5, 'd' => 2, 'height' => 2.7,
        'openings' => [
            ['type' => 'ДВ', 'width' => 0.9, 'height' => 2.1, 'wall' => 0.12, 'threshold' => false],
            ['type' => 'ОК', 'width' => 1.5, 'height' => 1.5],
        ],
    ]);

    expect((float) $g['perimeter'])->toBe(18.0)
        ->and((float) $g['floor'])->toBe(17.0)
        ->and((float) $g['openings_area'])->toBe(4.14)
        ->and((float) $g['walls'])->toBe(44.46)
        ->and((float) $g['floor_threshold'])->toBe(17.108)
        ->and((float) $g['perimeter_net'])->toBe(17.1);
});

it('считает гипсовую штукатурку этой комнаты с НДС 22 %', function () {
    $r = (new Estimator(catalog()))->estimate(['vat_rate' => 0.22, 'sections' => [
        ['name' => 'Стены', 'lines' => [['work_type' => 'plaster_gypsum_brick', 'qty' => 44.46]]],
    ]]);
    $line = $r['sections'][0]['lines'][0];

    expect((float) $line['work_sum'])->toBe(20229.30)
        ->and(array_map(fn ($m) => (float) $m['qty'], $line['materials']))->toBe([14.0, 890.0, 23.0, 6.0])
        ->and((float) $r['total'])->toBe(32712.78)
        ->and((float) $r['vat'])->toBe(5899.03);
});

it('округляет материалы на позиции, а не на каждом помещении', function () {
    // две комнаты по 10,01 м²: по отдельности маяков было бы 6 + 6, на позиции — ⌈10,01⌉ = 11
    $r = (new Estimator(catalog()))->estimate(['sections' => [
        ['name' => 'Стены', 'lines' => [['work_type' => 'plaster_gypsum_brick', 'bases' => ['walls' => 20.02]]]],
    ]]);
    expect((float) $r['sections'][0]['lines'][0]['materials'][2]['qty'])->toBe(11.0);
});
