<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Позиция сметы с готовым объёмом, без замеров */
class ObjectLine extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];
}
