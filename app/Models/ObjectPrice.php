<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Цена работы, материала или прочей позиции, изменённая для одного объекта */
class ObjectPrice extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    public const KINDS = ['work' => 'work_types', 'material' => 'materials', 'extra' => 'extras'];
}
