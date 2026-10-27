<?php

use App\Livewire\RoomForm;
use App\Models\CrmObject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    $this->object = CrmObject::create(['name' => 'Тест', 'status' => 'planning']);
});

it('не сохраняет помещение с нулевой площадью под выбранную работу', function () {
    Livewire::test(RoomForm::class, ['object' => $this->object])
        ->set('name', 'Кухня')->set('shape', 'rect')->set('a', '')->set('b', '')
        ->set('works.plaster_gypsum_brick.on', true)
        ->call('save')
        ->assertHasErrors('area');

    expect($this->object->rooms()->count())->toBe(0);
});

it('«Сохранить и следующее» сохраняет и открывает новое помещение', function () {
    Livewire::test(RoomForm::class, ['object' => $this->object])
        ->set('name', 'Кухня')->set('shape', 'rect')->set('a', '3,4')->set('b', '2.9')
        ->set('works.plaster_gypsum_brick.on', true)
        ->call('saveAndNext')
        ->assertHasNoErrors()
        ->assertRedirect(route('rooms.create', $this->object));

    $room = $this->object->rooms()->first();
    expect($room->name)->toBe('Кухня')
        ->and((float) $room->geometry()['floor'])->toBe(9.86)
        ->and($room->works()->pluck('work_type')->all())->toBe(['plaster_gypsum_brick']);
});

it('показывает шаги в пустом объекте и выходит из системы', function () {
    $this->get("/objects/{$this->object->id}")->assertOk()
        ->assertSee('Добавьте помещение')->assertSee('Добавить первое помещение');

    $this->post('/logout')->assertRedirect('/login');
    $this->get('/')->assertRedirect('/login');
});
