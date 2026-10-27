<?php

namespace App\Http\Controllers;

use App\Support\ConceptData;

/** Концепт следующих этапов на примерных данных — показать клиенту, куда растёт CRM. В базу не пишет */
class ConceptController extends Controller
{
    public const TABS = ['chess' => 'Шахматка', 'schedule' => 'График работ', 'brigades' => 'Бригады', 'today' => 'Сегодня (прораб)'];

    public function __invoke(string $tab = 'chess')
    {
        abort_unless(isset(self::TABS[$tab]), 404);

        return view("concept.$tab", [
            'tab' => $tab,
            'progress' => ConceptData::progress(),
            'chess' => ConceptData::chess(),
            'schedule' => ConceptData::schedule(),
            'brigades' => ConceptData::brigades(),
        ]);
    }
}
