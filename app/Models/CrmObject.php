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

    public function estimates(): HasMany
    {
        return $this->hasMany(Estimate::class, 'object_id')->latest();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
