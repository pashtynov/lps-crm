<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Прочие позиции «шт. × цена» по разделу: вывоз мусора */
class ObjectExtra extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];
}
