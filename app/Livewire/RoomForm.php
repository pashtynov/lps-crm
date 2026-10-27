<?php

namespace App\Livewire;

use App\Calc\Catalog;
use App\Calc\Geometry;
use App\Models\CrmObject;
use App\Models\Room;
use Livewire\Attributes\Layout;
use Livewire\Component;

/** Замер помещения полями — вместо графического редактора из вёрстки, от которого клиент отказался */
#[Layout('layouts.form')]
class RoomForm extends Component
{
    public CrmObject $object;

    public ?int $roomId = null;

    public string $name = '';
    public string $section = 'Основной раздел';
    public string $building = '';
    public string $entrance = '';
    public string $floor = '';
    public string $apartment = '';
    public string $kind = 'living';
    public string $shape = 'rect';
    public $a = null, $b = null, $c = null, $d = null, $f = null;
    public $floorArea = null, $perimeter = null;
    public $height = 2.7;
    public array $openings = [];
    /** [код вида работ => ['on' => bool, 'thickness' => мм, 'layers' => шт.]] */
    public array $works = [];

    public function mount(CrmObject $object, ?Room $room = null): void
    {
        $this->object = $object;
        foreach (Catalog::workTypes() as $code => $wt) {
            $this->works[$code] = ['on' => false] + ($wt['params'] ?? []);
        }

        if ($room?->exists) {
            abort_unless($room->object_id === $object->id, 404);
            $this->roomId = $room->id;
            $this->fill([
                'name' => $room->name, 'section' => $room->section, 'kind' => $room->kind, 'shape' => $room->shape,
                'building' => (string) $room->building, 'entrance' => (string) $room->entrance,
                'floor' => (string) $room->floor, 'apartment' => (string) $room->apartment,
                'a' => $room->a, 'b' => $room->b, 'c' => $room->c, 'd' => $room->d, 'f' => $room->f,
                'floorArea' => $room->floor_area, 'perimeter' => $room->perimeter, 'height' => $room->height,
                'openings' => $room->openings ?? [],
            ]);
            foreach ($room->works as $w) {
                if (isset($this->works[$w->work_type])) {
                    $this->works[$w->work_type] = ['on' => true] + ($w->params ?? []) + $this->works[$w->work_type];
                }
            }
        } else {
            // новое помещение наследует раздел и адрес последнего — быстрее заводить квартиру
            $last = $object->rooms()->latest('id')->first();
            if ($last) {
                $this->fill(['section' => $last->section, 'building' => (string) $last->building,
                    'entrance' => (string) $last->entrance, 'floor' => (string) $last->floor,
                    'apartment' => (string) $last->apartment, 'height' => $last->height]);
            }
            $this->openings = [['type' => 'ДВ', 'width' => 0.9, 'height' => 2.1, 'wall' => 0.12, 'threshold' => false]];
        }
    }

    public function addOpening(string $type): void
    {
        $this->openings[] = match ($type) {
            'ОК' => ['type' => 'ОК', 'width' => 1.5, 'height' => 1.5, 'wall' => null, 'threshold' => false],
            'ПР' => ['type' => 'ПР', 'width' => 0.9, 'height' => 2.1, 'wall' => 0.12, 'threshold' => false],
            default => ['type' => 'ДВ', 'width' => 0.9, 'height' => 2.1, 'wall' => 0.12, 'threshold' => false],
        };
    }

    public function removeOpening(int $i): void
    {
        unset($this->openings[$i]);
        $this->openings = array_values($this->openings);
    }

    public function geometry(): array
    {
        return Geometry::measure([
            'shape' => $this->shape, 'a' => $this->num($this->a), 'b' => $this->num($this->b),
            'c' => $this->num($this->c), 'd' => $this->num($this->d), 'f' => $this->num($this->f),
            'floor' => $this->num($this->floorArea), 'perimeter' => $this->num($this->perimeter),
            'height' => $this->num($this->height),
            'openings' => array_map(fn ($o) => [
                'type' => $o['type'], 'width' => $this->num($o['width'] ?? null),
                'height' => $this->num($o['height'] ?? null), 'wall' => $this->num($o['wall'] ?? null),
                'threshold' => ! empty($o['threshold']),
            ], $this->openings),
        ]);
    }

    /** «2,7» с запятой — так вводят на планшете */
    private function num($v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        $v = str_replace([',', ' '], ['.', ''], (string) $v);
        return is_numeric($v) ? (float) $v : null;
    }

    /** Сохранить и сразу открыть новое помещение — квартиру замеряют комнатами подряд */
    public function saveAndNext()
    {
        return $this->save(next: true);
    }

    /** Работы, у которых база для расчёта (стены, пол…) у этого замера нулевая */
    protected function zeroAreaWorks(): array
    {
        $g = $this->geometry();
        $types = Catalog::workTypes();

        return collect($this->works)
            ->filter(fn ($w, $code) => ! empty($w['on']) && isset($types[$code]) && (float) ($g[$types[$code]['basis']] ?? 0) <= 0)
            ->keys()->map(fn ($code) => $types[$code]['short'] ?? $types[$code]['name'])->all();
    }

    public function save(bool $next = false)
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'height' => 'required',
            'section' => 'required|string|max:255',
        ], [], ['name' => 'название', 'height' => 'высота', 'section' => 'раздел сметы']);

        // помещение с нулевой площадью молча ушло бы в смету нулём
        if ($zero = $this->zeroAreaWorks()) {
            $this->addError('area', 'Площадь для работ «' . implode('», «', $zero) . '» получается нулевой — проверьте размеры и высоту.');
            return null;
        }

        $data = [
            'name' => $this->name, 'section' => $this->section, 'kind' => $this->kind, 'shape' => $this->shape,
            'building' => $this->building ?: null, 'entrance' => $this->entrance ?: null,
            'floor' => $this->floor ?: null, 'apartment' => $this->apartment ?: null,
            'a' => $this->num($this->a), 'b' => $this->num($this->b), 'c' => $this->num($this->c),
            'd' => $this->num($this->d), 'f' => $this->num($this->f),
            'floor_area' => $this->num($this->floorArea), 'perimeter' => $this->num($this->perimeter),
            'height' => $this->num($this->height), 'openings' => $this->geometryOpenings(),
        ];
        $room = $this->roomId ? Room::findOrFail($this->roomId) : new Room(['object_id' => $this->object->id]);
        $room->fill($data)->save();

        $room->works()->delete();
        foreach ($this->works as $code => $w) {
            if (! empty($w['on'])) {
                $params = array_filter(array_diff_key($w, ['on' => 1]), fn ($v) => $v !== null && $v !== '');
                $room->works()->create(['work_type' => $code, 'params' => array_map(fn ($v) => $this->num($v), $params)]);
            }
        }

        session()->flash('status', "Помещение «{$room->name}» сохранено" . ($next ? ' — заводим следующее' : ''));

        return $next
            ? $this->redirectRoute('rooms.create', $this->object)
            : $this->redirectRoute('objects.show', $this->object);
    }

    private function geometryOpenings(): array
    {
        return array_map(fn ($o) => [
            'type' => $o['type'], 'width' => $this->num($o['width'] ?? null), 'height' => $this->num($o['height'] ?? null),
            'wall' => $this->num($o['wall'] ?? null), 'threshold' => ! empty($o['threshold']),
        ], $this->openings);
    }

    public function render()
    {
        return view('livewire.room-form', [
            'g' => $this->geometry(),
            'zeroArea' => $this->zeroAreaWorks(),
            'room' => $this->roomId ? Room::find($this->roomId) : null,
            // без цен — скрыты, кроме уже выбранных в этом помещении
            'workTypes' => array_filter(Catalog::workTypes(), fn ($wt, $code) => isset(Catalog::activeWorkTypes()[$code]) || ! empty($this->works[$code]['on']), ARRAY_FILTER_USE_BOTH),
            'sections' => $this->object->rooms()->reorder()->distinct()->pluck('section')
                ->merge($this->object->lines()->reorder()->distinct()->pluck('section'))->unique()->values(),
        ])->title(($this->roomId ? 'Помещение' : 'Новое помещение') . ' — ' . $this->object->name);
    }
}
