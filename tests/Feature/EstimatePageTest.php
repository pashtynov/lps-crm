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
    $this->get("/objects/{$object->id}/rooms/{$room->id}")->assertOk()->assertSee('Площади');
    $this->get("/objects/{$object->id}/rooms/new")->assertOk();
    $this->get('/catalog')->assertOk()->assertSee('Бетоноконтакт');
});
