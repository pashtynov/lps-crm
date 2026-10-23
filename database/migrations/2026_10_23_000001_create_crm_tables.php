<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Этап 1: объект → помещения с замером → смета. Иерархия дом/подъезд/этаж — пока подписями
// у помещения, отдельными таблицами — этап 2 (docs/crm-plan.md в репозитории adheads).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objects', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('address')->nullable();
            $t->string('customer')->nullable();
            $t->string('status')->default('planning'); // planning | work | done | archive
            $t->decimal('vat_rate', 5, 4)->default(0.22);
            $t->text('note')->nullable();
            $t->timestamps();
        });

        Schema::create('rooms', function (Blueprint $t) {
            $t->id();
            $t->foreignId('object_id')->constrained()->cascadeOnDelete();
            $t->string('section')->default('Основной раздел'); // раздел сметы: «ниже отм. 0,000» и т. п.
            $t->string('building')->nullable();
            $t->string('entrance')->nullable();
            $t->string('floor')->nullable();
            $t->string('apartment')->nullable();
            $t->string('name');
            $t->string('kind')->default('living'); // living | kitchen | bath | hall | loggia | mop | tech
            $t->string('shape')->default('rect');  // rect | l | manual
            foreach (['a', 'b', 'c', 'd', 'f', 'floor_area', 'perimeter', 'height'] as $c) {
                $t->decimal($c, 10, 3)->nullable();
            }
            $t->json('openings')->nullable();
            $t->string('source')->default('measure'); // measure | project
            $t->timestamps();
        });

        Schema::create('room_works', function (Blueprint $t) {
            $t->id();
            $t->foreignId('room_id')->constrained()->cascadeOnDelete();
            $t->string('work_type');
            $t->json('params')->nullable();
            $t->unique(['room_id', 'work_type']);
        });

        // Готовая площадь без замеров — как сметчик считает сейчас (демо Бетанкура)
        Schema::create('object_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('object_id')->constrained()->cascadeOnDelete();
            $t->string('section')->default('Основной раздел');
            $t->string('work_type');
            $t->decimal('qty', 12, 3);
            $t->unsignedInteger('position')->default(0);
        });

        Schema::create('object_extras', function (Blueprint $t) {
            $t->id();
            $t->foreignId('object_id')->constrained()->cascadeOnDelete();
            $t->string('section')->default('Основной раздел');
            $t->string('extra');
            $t->decimal('qty', 12, 3);
        });

        // Вариант сметы — снимок расчёта с ценами на момент сохранения
        Schema::create('estimates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('object_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->json('result');
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['estimates', 'object_extras', 'object_lines', 'room_works', 'rooms', 'objects'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
