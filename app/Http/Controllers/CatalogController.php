<?php

namespace App\Http\Controllers;

use App\Calc\Catalog;

class CatalogController extends Controller
{
    public function __invoke()
    {
        return view('catalog', [
            'workTypes' => Catalog::workTypes(),
            'materials' => Catalog::materials(),
            'extras' => Catalog::extras(),
        ]);
    }
}
