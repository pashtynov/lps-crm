<?php

namespace App\Http\Controllers;

use App\Calc\Dec;
use App\Models\CrmObject;
use App\Models\Room;
use Illuminate\Http\Request;

class ObjectController extends Controller
{
    public function index()
    {
        $objects = CrmObject::withCount('rooms')->latest()->get();

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
        $totals = ['floor' => '0', 'walls' => '0', 'ceiling' => '0'];
        $rooms = $object->rooms->map(function (Room $r) use (&$totals) {
            $g = $r->geometry();
            foreach ($totals as $k => $v) {
                $totals[$k] = Dec::add($v, $g[$k]);
            }
            return ['room' => $r, 'g' => $g];
        });

        return view('objects.show', compact('object', 'rooms', 'totals'));
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
