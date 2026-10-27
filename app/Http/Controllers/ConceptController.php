<?php

namespace App\Http\Controllers;

use App\Support\ConceptData as C;

/**
 * Концепт следующих этапов на примерных данных — иерархия макета 2023 года:
 * Объект → Дом → Подъезд (шахматка квартир и МОП) → Квартира с помещениями. В базу не пишет.
 */
class ConceptController extends Controller
{
    public const TABS = ['houses' => 'Дома', 'schedule' => 'График работ', 'brigades' => 'Бригады', 'today' => 'Сегодня (прораб)'];

    public function object(string $tab = 'houses')
    {
        abort_unless(isset(self::TABS[$tab]), 404);

        return view("concept.$tab", [
            'tab' => $tab,
            'progress' => C::progress(),
            'schedule' => C::schedule(),
            'brigades' => C::brigades(),
        ]);
    }

    public function house(int $house)
    {
        abort_unless(isset(C::HOUSES[$house]), 404);

        return view('concept.house', ['house' => $house, 'h' => C::HOUSES[$house]]);
    }

    public function section(int $house, int $section, string $view = 'chess')
    {
        abort_unless(isset(C::HOUSES[$house]['sections'][$section]) && in_array($view, ['chess', 'mop'], true), 404);

        return view('concept.section', [
            'house' => $house, 'section' => $section, 'view' => $view, 'h' => C::HOUSES[$house],
            'data' => C::house($house)[$section],
        ]);
    }

    public function flat(int $house, int $n)
    {
        $flat = C::flat($house, $n);
        abort_unless($flat, 404);

        return view('concept.flat', ['house' => $house, 'h' => C::HOUSES[$house], 'flat' => $flat]);
    }

    public function room(int $house, int $n, int $i)
    {
        $flat = C::flat($house, $n);
        abort_unless($flat && isset($flat['rooms'][$i]), 404);

        return view('concept.room', ['house' => $house, 'h' => C::HOUSES[$house], 'flat' => $flat, 'i' => $i, 'room' => $flat['rooms'][$i]]);
    }

    /** Мастер «Новый объект»: объект → планировки → дома и подъезды → проверка. Сохраняется в браузере */
    public function create()
    {
        return view('concept.new');
    }

    /** Объект, созданный мастером: данные читаются из браузера по ?id= */
    public function built()
    {
        return view('concept.built');
    }
}
