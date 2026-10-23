<?php

namespace App\Models;

use App\Calc\Geometry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['openings' => 'array'];

    public const KINDS = [
        'living' => 'Жилая комната',
        'kitchen' => 'Кухня',
        'bath' => 'Санузел',
        'hall' => 'Коридор, прихожая',
        'loggia' => 'Лоджия, балкон',
        'mop' => 'МОП',
        'tech' => 'Техпомещение',
    ];

    public const SHAPES = [
        'rect' => 'Прямоугольник',
        'l' => 'Г-образное (A–F)',
        'manual' => 'Площадь и периметр вручную',
    ];

    public function object(): BelongsTo
    {
        return $this->belongsTo(CrmObject::class, 'object_id');
    }

    public function works(): HasMany
    {
        return $this->hasMany(RoomWork::class);
    }

    /** Площади и длины по замеру — строки bcmath, см. App\Calc\Geometry */
    public function geometry(): array
    {
        return Geometry::measure([
            'shape' => $this->shape,
            'a' => $this->a, 'b' => $this->b, 'c' => $this->c, 'd' => $this->d, 'f' => $this->f,
            'floor' => $this->floor_area, 'perimeter' => $this->perimeter,
            'height' => $this->height,
            'openings' => $this->openings ?? [],
        ]);
    }

    /** Квартира / этаж / подъезд одной строкой для таблиц */
    public function place(): string
    {
        return collect([
            $this->building ? "дом {$this->building}" : null,
            $this->entrance ? "подъезд {$this->entrance}" : null,
            $this->floor ? "этаж {$this->floor}" : null,
            $this->apartment ? "кв. {$this->apartment}" : null,
        ])->filter()->implode(', ');
    }
}
