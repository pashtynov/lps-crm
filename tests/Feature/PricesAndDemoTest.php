<?php

use App\Calc\ObjectEstimate;
use App\Livewire\EstimateView;
use App\Models\CrmObject;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DemoSeeder::class);
    $this->actingAs(User::factory()->create());
    $this->betancura = CrmObject::where('name', 'Бетанкура, штукатурка')->firstOrFail();
});

it('меняет цену работы только для объекта и возвращает справочную', function () {
    $c = Livewire::test(EstimateView::class, ['object' => $this->betancura])
        ->set('prices.work__plaster_gypsum_brick', '470');

    // 26 269,4 м² × 15 ₽ = 394 041 ₽ сверху
    expect((float) ObjectEstimate::calculate($this->betancura)['total'])->toBe(33114280.11);
    $c->assertSee('Изменено цен: 1');

    // другой объект не задет
    $flat = CrmObject::where('name', 'Демо: квартира')->first();
    expect((float) ObjectEstimate::catalog($flat)['work_types'][3]['price'])->toBe(455.0);

    $c->set('prices.work__plaster_gypsum_brick', '455');
    expect($this->betancura->prices()->count())->toBe(0)
        ->and((float) ObjectEstimate::calculate($this->betancura)['total'])->toBe(32720239.11);
});

it('вариант сметы запоминает изменённую цену, сброс цен его не трогает', function () {
    $c = Livewire::test(EstimateView::class, ['object' => $this->betancura])
        ->set('prices.material__plaster_gypsum', '13')
        ->set('variantTitle', 'дороже смесь')->call('saveVariant')
        ->call('resetPrices');

    expect($this->betancura->prices()->count())->toBe(0)
        ->and((float) $this->betancura->estimates()->first()->result['total'])->toBeGreaterThan(32720239.11);
});

it('сброс демо возвращает объекты и не трогает остальные', function () {
    $this->betancura->prices()->create(['kind' => 'work', 'code' => 'plaster_gypsum_brick', 'price' => 470]);
    $mine = CrmObject::create(['name' => 'Рабочий объект', 'status' => 'work']);

    $this->artisan('crm:demo-reset', ['--force' => true])->assertSuccessful();

    $fresh = CrmObject::where('name', 'Бетанкура, штукатурка')->firstOrFail();
    expect((float) ObjectEstimate::calculate($fresh)['total'])->toBe(32720239.11)
        ->and(CrmObject::whereKey($mine->id)->exists())->toBeTrue()
        ->and(CrmObject::whereIn('name', DemoSeeder::NAMES)->count())->toBe(2);
});
