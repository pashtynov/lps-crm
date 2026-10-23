<?php

namespace App\Livewire;

use App\Calc\Catalog;
use App\Calc\ObjectEstimate;
use App\Models\CrmObject;
use App\Models\Estimate;
use Livewire\Attributes\Layout;
use Livewire\Component;

/** Смета по объекту: считается на лету, вариант можно сохранить снимком */
#[Layout('layouts.app')]
class EstimateView extends Component
{
    public CrmObject $object;

    public $vatPercent = 22;

    // новая позиция с готовым объёмом
    public string $newSection = 'Основной раздел';
    public string $newWorkType = '';
    public $newQty = null;

    /** [md5(раздел) => контейнеров] — в названиях разделов точки, а точка для Livewire — вложенность */
    public array $waste = [];

    public string $variantTitle = '';

    public function mount(CrmObject $object): void
    {
        $this->object = $object;
        $this->vatPercent = round((float) $object->vat_rate * 100, 2);
        $this->newWorkType = array_key_first(Catalog::workTypes());
        foreach ($object->extras()->where('extra', 'waste_container')->get() as $e) {
            $this->waste[md5($e->section)] = (float) $e->qty;
        }
    }

    public function updatedVatPercent(): void
    {
        $v = (float) str_replace(',', '.', (string) $this->vatPercent);
        $this->object->update(['vat_rate' => max(0, min($v, 30)) / 100]);
    }

    public function updatedWaste($value, $key): void
    {
        $section = collect(ObjectEstimate::spec($this->object->fresh())['sections'])->pluck('name')
            ->first(fn ($name) => md5($name) === $key);
        if ($section === null) {
            return;
        }
        $qty = (float) str_replace(',', '.', (string) $value);
        $this->object->extras()->where('extra', 'waste_container')->where('section', $section)->delete();
        if ($qty > 0) {
            $this->object->extras()->create(['section' => $section, 'extra' => 'waste_container', 'qty' => $qty]);
        }
    }

    public function addLine(): void
    {
        $qty = (float) str_replace([',', ' '], ['.', ''], (string) $this->newQty);
        $this->validate(['newSection' => 'required|string|max:255', 'newWorkType' => 'required']);
        if ($qty <= 0 || ! isset(Catalog::workTypes()[$this->newWorkType])) {
            $this->addError('newQty', 'Укажите объём больше нуля');
            return;
        }
        $this->object->lines()->create([
            'section' => $this->newSection, 'work_type' => $this->newWorkType, 'qty' => $qty,
            'position' => (int) $this->object->lines()->max('position') + 1,
        ]);
        $this->newQty = null;
    }

    public function removeLine(int $id): void
    {
        $this->object->lines()->whereKey($id)->delete();
    }

    public function saveVariant(): void
    {
        $title = trim($this->variantTitle) ?: 'Вариант от ' . now()->format('d.m.Y H:i');
        Estimate::create([
            'object_id' => $this->object->id, 'title' => $title,
            'result' => ObjectEstimate::calculate($this->object->fresh()), 'user_id' => auth()->id(),
        ]);
        $this->variantTitle = '';
        session()->flash('status', "Сохранён «{$title}»");
    }

    public function render()
    {
        $object = $this->object->fresh();
        $result = ObjectEstimate::calculate($object);
        $sections = collect($result['sections'])->pluck('name');

        return view('livewire.estimate-view', [
            'result' => $result,
            'workTypes' => Catalog::workTypes(),
            'lines' => $object->lines()->get(),
            'sections' => $sections->isEmpty() ? collect(['Основной раздел']) : $sections,
            'variants' => $object->estimates()->get(),
        ])->title('Смета — ' . $object->name);
    }
}
