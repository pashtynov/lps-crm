<?php

namespace App\Http\Controllers;

use App\Models\CrmObject;
use App\Support\ObjectSummary;
use App\Models\Room;
use Illuminate\Http\Request;

class ObjectController extends Controller
{
    public function index()
    {
        $objects = CrmObject::withCount(['rooms', 'lines'])->latest()->get();

        return view('objects.index', compact('objects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'customer' => 'nullable|string|max:255',
        ]);
        $object = CrmObject::create($data + ['status' => 'planning']);

        return redirect()->route('objects.show', $object);
    }

    public function show(CrmObject $object)
    {
        $object->load('rooms.works', 'lines', 'estimates');
        $rooms = $object->rooms->map(fn (Room $r) => ['room' => $r, 'g' => $r->geometry()]);
        $sum = ObjectSummary::for($object);

        return view('objects.show', compact('object', 'rooms', 'sum'));
    }

    public function edit(CrmObject $object)
    {
        return view('objects.edit', compact('object'));
    }

    public function update(Request $request, CrmObject $object)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'customer' => 'nullable|string|max:255',
            'status' => 'required|in:' . implode(',', array_keys(CrmObject::STATUSES)),
            'vat_percent' => 'required|numeric|min:0|max:30',
            'note' => 'nullable|string|max:5000',
        ]);
        $data['vat_rate'] = $data['vat_percent'] / 100;
        unset($data['vat_percent']);
        $object->update($data);

        return redirect()->route('objects.show', $object)->with('status', 'Объект сохранён');
    }

    public function destroy(CrmObject $object)
    {
        $name = $object->name;
        $object->delete();

        return redirect()->route('objects.index')->with('status', "Объект «{$name}» удалён");
    }

    public function copyRoom(CrmObject $object, Room $room)
    {
        abort_unless($room->object_id === $object->id, 404);
        $copy = $room->replicate();
        $copy->name = $room->name . ' (копия)';
        $copy->save();
        foreach ($room->works as $w) {
            $copy->works()->create(['work_type' => $w->work_type, 'params' => $w->params]);
        }

        return redirect()->route('rooms.edit', [$object, $copy]);
    }

    public function deleteRoom(CrmObject $object, Room $room)
    {
        abort_unless($room->object_id === $object->id, 404);
        $room->delete();

        return redirect()->route('objects.show', $object);
    }
}
