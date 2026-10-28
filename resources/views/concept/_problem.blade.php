{{-- Поле «Проблема»: видно и правится здесь, на шахматке, в сводках и у прораба. $house, $ns — JS-выражения (номер квартиры или массив) --}}
@include('concept._problems_js')
<div class="crm-problem" x-data="problemEdit({{ $house }}, {{ $ns }})" @lps-problems.document="rev++">
    <template x-for="i in items()" :key="i.n">
        <div class="crm-warning crm-problem__box" x-show="!editing">
            <span x-text="'⚠ ' + (many() ? `кв. ${i.n}: ` : '') + i.text"></span>
            <span class="crm-problem__btns">
                <button type="button" class="btn btn-link btn-sm" @click="edit(i.n)">Изменить</button>
                <button type="button" class="btn btn-link btn-sm" @click="clear(i.n)">Снять</button>
            </span>
        </div>
    </template>
    <button type="button" class="btn btn-light btn-sm crm-problem__add" x-show="!editing && (many() || !items().length)" @click="edit()">⚠ {{ $addLabel ?? 'Отметить проблему' }}</button>
    <div class="crm-problem__form" x-show="editing" x-cloak>
        <select class="form-select form-select-sm mb-2" x-show="many()" x-model.number="n" @change="pick()">
            <template x-for="q in ns" :key="q"><option :value="q" :selected="q === n" x-text="`кв. ${q}`"></option></template>
        </select>
        <textarea class="form-control" rows="2" x-ref="ta" x-model="draft" @keydown.ctrl.enter="save()" @keydown.meta.enter="save()"
                  placeholder="Что мешает работать: нет доступа, протечка, брак основания…"></textarea>
        <div class="crm-problem__actions">
            <button type="button" class="btn btn-accent btn-sm" @click="save()" x-text="draft.trim() ? 'Сохранить' : (items().some(i => i.n === n) ? 'Снять проблему' : 'Сохранить')"></button>
            <button type="button" class="btn btn-light btn-sm" @click="editing = false">Отменить</button>
        </div>
    </div>
    @isset($hint)<small class="crm-muted crm-problem__hint" x-show="items().length && !editing">{{ $hint }}</small>@endisset
</div>
