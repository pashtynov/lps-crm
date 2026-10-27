<?php

use App\Models\User;
use App\Support\ConceptData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('открывает все экраны концепта и не пускает без входа', function () {
    $this->get('/concept')->assertRedirect('/login');
    $this->actingAs(User::factory()->create());

    $this->get('/concept')->assertOk()->assertSee('Концепт следующих этапов')->assertSee('Слой:')->assertSee('Выполнено на');
    $this->get('/concept/schedule')->assertOk()->assertSee('сегодня');
    $this->get('/concept/brigades')->assertOk()->assertSee('Нужна бригада');
    $this->get('/concept/today')->assertOk()->assertSee('Закрыть день');
    $this->get('/concept/nope')->assertNotFound();
});

it('считает прогресс по м², а не по числу квартир', function () {
    $p = ConceptData::progress();
    expect($p['plaster']['total'])->toBeGreaterThan(10000)
        ->and($p['screed']['total'])->toBeGreaterThan(5000)
        ->and($p['plaster']['percent'])->toBeGreaterThan($p['putty']['percent']);
});
