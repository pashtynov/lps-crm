<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Цена работы или материала для конкретного объекта — «а если 470 вместо 455?» без правки справочника
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('object_prices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('object_id')->constrained()->cascadeOnDelete();
            $t->string('kind'); // work | material | extra
            $t->string('code');
            $t->decimal('price', 12, 2);
            $t->unique(['object_id', 'kind', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('object_prices');
    }
};
