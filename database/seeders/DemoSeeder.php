<?php

namespace Database\Seeders;

use App\Models\CrmObject;
use Illuminate\Database\Seeder;

/** Демо-объекты для показа клиенту. Вернуть в исходное состояние — php artisan crm:demo-reset */
class DemoSeeder extends Seeder
{
    public const NAMES = ['Бетанкура, штукатурка', 'Демо: квартира'];

    public function run(): void
    {
        $this->betancura();
        $this->apartment();
    }

    /** Смета Бетанкура 20.05.2024 готовыми объёмами — эталон: 32 720 239,11 ₽ при НДС 20 % */
    private function betancura(): void
    {
        $fx = json_decode(file_get_contents(base_path('tests/fixtures/betancura-2024-05-20.json')), true);
        $o = CrmObject::firstOrCreate(['name' => 'Бетанкура, штукатурка'], [
            'address' => 'Нижний Новгород · смета от 20.05.2024', 'status' => 'done', 'vat_rate' => $fx['vat_rate'],
            'note' => 'Демо: объёмы и цены из сметы клиента, итог должен совпасть до копейки',
        ]);
        if ($o->lines()->exists()) {
            return;
        }
        $pos = 0;
        foreach ($fx['sections'] as $s) {
            foreach ($s['lines'] as $l) {
                $o->lines()->create(['section' => $s['name'], 'work_type' => $l['work_type'], 'qty' => $l['qty'], 'position' => ++$pos]);
            }
            foreach ($s['extras'] as $e) {
                $o->extras()->create(['section' => $s['name'], 'extra' => $e['code'], 'qty' => $e['qty']]);
            }
        }
    }

    /** Квартира с замерами: смета собирается из помещений */
    private function apartment(): void
    {
        $o = CrmObject::firstOrCreate(['name' => 'Демо: квартира'], [
            'address' => 'пример', 'status' => 'planning', 'vat_rate' => 0.22,
        ]);
        if ($o->rooms()->exists()) {
            return;
        }
        $door = fn ($w = 0.8) => ['type' => 'ДВ', 'width' => $w, 'height' => 2.1, 'wall' => 0.12, 'threshold' => false];
        $window = fn ($w = 1.5) => ['type' => 'ОК', 'width' => $w, 'height' => 1.5, 'wall' => null, 'threshold' => false];
        $place = ['section' => 'Отделка квартиры', 'building' => '1', 'entrance' => '1', 'floor' => '3', 'apartment' => '12', 'height' => 2.7];
        $rooms = [
            ['name' => 'Комната 1', 'kind' => 'living', 'shape' => 'l', 'a' => 4, 'f' => 5, 'c' => 1.5, 'd' => 2, 'openings' => [$door(0.9), $window()]],
            ['name' => 'Комната 2', 'kind' => 'living', 'shape' => 'rect', 'a' => 4.2, 'b' => 3.1, 'openings' => [$door(), $window(1.2)]],
            ['name' => 'Кухня', 'kind' => 'kitchen', 'shape' => 'rect', 'a' => 3.4, 'b' => 2.9, 'openings' => [$door(), $window(1.2)]],
            ['name' => 'Прихожая', 'kind' => 'hall', 'shape' => 'manual', 'floor_area' => 6.8, 'perimeter' => 11.6, 'openings' => [$door(0.9)]],
        ];
        foreach ($rooms as $r) {
            $room = $o->rooms()->create($place + $r);
            $room->works()->create(['work_type' => 'plaster_gypsum_brick', 'params' => []]);
        }
        $o->extras()->create(['section' => 'Отделка квартиры', 'extra' => 'waste_container', 'qty' => 1]);
    }
}
