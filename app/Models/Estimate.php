<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Сохранённый вариант сметы — снимок расчёта, справочник на него больше не влияет */
class Estimate extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['result' => 'array'];

    public function object(): BelongsTo
    {
        return $this->belongsTo(CrmObject::class, 'object_id');
    }
}
