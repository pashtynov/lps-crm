<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Объект (ЖК, дом, частный заказ). В PHP слово Object занято — отсюда CrmObject. */
class CrmObject extends Model
{
    protected $table = 'objects';

    protected $guarded = ['id'];

    protected $casts = ['vat_rate' => 'decimal:4'];

    public const STATUSES = [
        'planning' => 'Планирование',
        'work' => 'В работе',
        'done' => 'Завершён',
        'archive' => 'Архив',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'object_id')->orderBy('section')->orderBy('id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ObjectLine::class, 'object_id')->orderBy('position');
    }

    public function extras(): HasMany
    {
        return $this->hasMany(ObjectExtra::class, 'object_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ObjectPrice::class, 'object_id');
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(Estimate::class, 'object_id')->latest();
    }

    /** Цвет карточки, класс бейджа и иконка — как в макете списка объектов */
    public const STATUS_STYLE = [
        'planning' => ['#9A52DF', 'badge-purple', 'time.svg'],
        'work' => ['#467CF4', 'badge-primary', 'play.svg'],
        'done' => ['#1DBA6F', 'badge-success', 'done.svg'],
        'archive' => ['#8B9298', 'badge-secondary', 'archive.svg'],
    ];

    public function statusStyle(): array
    {
        return self::STATUS_STYLE[$this->status] ?? self::STATUS_STYLE['planning'];
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
