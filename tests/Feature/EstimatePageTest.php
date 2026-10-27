<?php

use App\Models\CrmObject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('без входа отправляет на страницу логина', function () {
    $this->get('/')->assertRedirect('/login');
});

it('показывает смету Бетанкура с итогом из сметы клиента и отдаёт Excel и PDF', function () {
    $this->seed(DatabaseSeeder::class);
    $object = CrmObject::where('name', 'like', 'Бетанкура%')->firstOrFail();
    $this->actingAs(User::factory()->create());

    $this->get("/objects/{$object->id}/estimate")->assertOk()->assertSee('32 720 239,11');
    $this->get("/objects/{$object->id}/estimate.xlsx")->assertOk()->assertDownload();
    $this->get("/objects/{$object->id}/estimate.pdf")->assertOk()->assertHeader('Content-Type', 'application/pdf');
});

it('открывает объект, форму помещения и справочник', function () {
    $this->seed(DatabaseSeeder::class);
    $object = CrmObject::where('name', 'like', 'Демо%')->firstOrFail();
    $room = $object->rooms()->first();
    $this->actingAs(User::factory()->create());

    $this->get("/objects/{$object->id}")->assertOk()->assertSee($room->name);
    $this->get("/objects/{$object->id}/rooms/{$room->id}")->assertOk()->assertSee('Площадь стен')->assertSee('Замер');
    $this->get("/objects/{$object->id}/rooms/new")->assertOk();
    $this->get('/catalog')->assertOk()->assertSee('Бетоноконтакт');
    $this->get("/objects/{$object->id}/edit")->assertOk()->assertSee('Редактирование объекта');
    $this->get("/objects/{$object->id}/estimate")->assertOk()->assertSee('Доля в смете');
});

it('прячет работы без цен и объясняет объёмы без замера', function () {
    $this->seed(DatabaseSeeder::class);
    $this->actingAs(User::factory()->create());
    $flat = CrmObject::where('name', 'like', 'Демо%')->firstOrFail();
    $betancura = CrmObject::where('name', 'like', 'Бетанкура%')->firstOrFail();

    $this->get("/objects/{$flat->id}/rooms/new")->assertOk()
        ->assertDontSee('Полусухая стяжка')->assertSee('Гипс. штукатурка')->assertSee('Дополнительно');
    $this->get("/objects/{$flat->id}/estimate")->assertOk()
        ->assertSee('+ Объём без замера')->assertDontSee('Нет цены работы');
    // у объекта без помещений объёмы раскрыты сразу
    $this->get("/objects/{$betancura->id}/estimate")->assertOk()->assertSee('Скрыть')->assertSee('Гипс. штукатурка');
    $this->get("/objects/{$flat->id}")->assertSee('замерено<br>помещений', false);
});
