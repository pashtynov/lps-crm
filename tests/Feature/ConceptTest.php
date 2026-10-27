<?php

use App\Models\User;
use App\Support\ConceptData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('открывает все экраны концепта и не пускает без входа', function () {
    $this->get('/concept')->assertRedirect('/login');
    $this->actingAs(User::factory()->create())->get('/')->assertSee('ЖК «Новая Высота»')->assertSee(route('concept'));
    auth()->logout();
    $this->get('/concept')->assertRedirect('/login');
    $this->actingAs(User::factory()->create());

    $this->get('/concept')->assertOk()->assertSee('Выполнено на')->assertSee('Дом 1')->assertSee('Дом 2');
    $this->get('/concept/house/2')->assertOk()->assertSee('Подъезд 1')->assertSee('готовность');
    $this->get('/concept/house/2/entrance/1')->assertOk()->assertSee('Слой:')->assertSee('МОП');
    $this->get('/concept/house/2/entrance/1/mop')->assertOk()->assertSee('Лестничная клетка')->assertSee('Техподполье');
    $this->get('/concept/house/2/flat/47')->assertOk()->assertSee('Квартира 47')->assertSee('Нет доступа')->assertSee('Кухня');
    $this->get('/concept/house/9')->assertNotFound();
    $this->get('/concept/house/2/flat/9999')->assertNotFound();
    $this->get('/concept/schedule')->assertOk()->assertSee('сегодня');
    $this->get('/concept/brigades')->assertOk()->assertSee('Нужна бригада');
    $this->get('/concept/today')->assertOk()->assertSee('Закрыть день');
    $this->get('/concept/nope')->assertNotFound();
});

it('считает прогресс по м², а не по числу квартир', function () {
    $p = ConceptData::progress(2);
    expect($p['plaster']['total'])->toBeGreaterThan(10000)
        ->and($p['screed']['total'])->toBeGreaterThan(5000)
        ->and($p['plaster']['percent'])->toBeGreaterThan($p['putty']['percent'])
        // дом 1 почти сдан, дом 2 в работе
        ->and(ConceptData::overall(1))->toBeGreaterThan(ConceptData::overall(2));

    // площадь квартиры — сумма помещений из замера
    $flat = ConceptData::flat(2, 47);
    expect($flat['walls'])->toBe(round(array_sum(array_column($flat['rooms'], 'walls')), 1));
});
