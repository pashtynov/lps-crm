<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomWork extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['params' => 'array'];
}
