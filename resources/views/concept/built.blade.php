@extends('layouts.app', ['title' => 'Объект из мастера — концепт', 'bodyClass' => 'object-page'])

@section('content')
    <script src="{{ asset('js/concept-builder.js') }}?v=1"></script>

    <div x-data="conceptBuilt(@js(route('objects.index')))">
        <template x-if="!obj">
            <div class="container-fluid">
                @include('concept._page', ['crumbs' => ['Объект из мастера' => '#'], 'title' => 'Объект не найден'])
                <div class="crm-card">Объект хранится в этом браузере, а здесь его нет — возможно, он создан на другом компьютере или удалён.
                    <a class="btn btn-accent ms-2" href="{{ route('concept.new') }}">Создать в мастере</a></div>
            </div>
        </template>

        <template x-if="obj">
            <div>
                <div class="page-head">
                    <div class="container-fluid">
                        <div class="page-head__row row align-items-center">
                            <div class="col"><nav aria-label="breadcrumb"><ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('objects.index') }}"><span>Объекты</span></a></li>
                                <li class="breadcrumb-item active"><a href="#"><span x-text="obj.name"></span></a></li>
                            </ol></nav></div>
                            <div class="page-head__col col-md-auto order-md-2"><span class="crm-concept-badge" title="Хранится в этом браузере, в базу CRM не пишется">концепт · создан в мастере</span></div>
                            <div class="page-head__col page-head__col--title col-lg order-md-4">
                                <h1><span x-text="obj.name"></span> <span class="crm-status" style="--c:#9A52DF">Планирование</span></h1>
                                <div class="crm-muted" x-text="[obj.address, obj.customer].filter(Boolean).join(' · ')"></div>
                            </div>
                            <div class="col-12 d-none d-md-block m-0 p-0 order-md-3"></div>
                        </div>
                    </div>
                </div>

                <div class="container-fluid">
                    <div class="crm-kpis crm-kpis--built">
                        <div class="crm-kpi"><span>Квартир</span><b data-flash="b-flats" x-text="fmt(ex().totals.flats)"></b><small x-text="`${ex().totals.houses} дом(а) · ${ex().totals.sections} подъезд(ов)`"></small></div>
                        <div class="crm-kpi"><span>МОП</span><b data-flash="b-mop" x-text="fmt(ex().totals.mop)"></b><small>холлы, лестницы, тамбуры</small></div>
                        <div class="crm-kpi"><span>Помещений в квартирах</span><b data-flash="b-rooms" x-text="fmt(ex().totals.rooms)"></b><small>из типовых планировок</small></div>
                        <div class="crm-kpi"><span>Стены / пол</span><b data-flash="b-walls" x-text="`${fmt(ex().totals.walls)} / ${fmt(ex().totals.floor)}`"></b><small>м² — сразу в смету</small></div>
                    </div>

                    <div class="crm-tabs-row crm-concept-tabs">
                        <template x-for="(h, hi) in obj.houses" :key="hi">
                            <button type="button" class="toggle-block__btn btn btn-outline-light" :class="house === hi && 'active'" @click="house = hi; selected = []" x-text="h.name"></button>
                        </template>
                        <button type="button" class="btn btn-light" @click="copyHouseHere()">Копировать дом</button>
                        <button type="button" class="btn btn-light" @click="addHouseHere()">+ дом</button>
                    </div>

                    <div class="crm-legend">
                        <template x-for="code in layoutCodes()" :key="code">
                            <span><i :style="`background:${color(code)}`"></i><span x-text="`${code} — ${ex().totals.byLayout[code] || 0} кв.`"></span></span>
                        </template>
                    </div>

                    <div class="crm-built">
                        <template x-for="(s, si) in ex().houses[house].sectionsX" :key="house + '-' + si">
                            <div class="crm-panel crm-built-section">
                                <div class="crm-built-section__head">
                                    <b x-text="`Подъезд ${si + 1}`"></b>
                                    <span class="crm-muted" x-text="`${s.floors} эт. · ${sectionFlats(s)} кв. · МОП ${s.mop.length}`"></span>
                                </div>
                                <div class="crm-built-section__tools">
                                    <button type="button" class="btn btn-light btn-sm" @click="addFloor(si)">+ этаж</button>
                                    <button type="button" class="btn btn-light btn-sm" @click="copySectionHere(si)">Копировать подъезд</button>
                                    <button type="button" class="btn btn-light btn-sm" @click="removeSection(house, si); selected = []" :disabled="obj.houses[house].sections.length < 2">Удалить</button>
                                </div>
                                <div class="crm-chess-section">
                                    <template x-for="fl in s.floorsList" :key="fl.floor">
                                        <div class="crm-chess-row">
                                            <button type="button" class="crm-chess-floor" @click="selectFloor(si, fl.floor, fl.flats.length)" :title="'Выделить этаж ' + fl.floor" x-text="fl.floor"></button>
                                            <template x-for="f in fl.flats" :key="f.n">
                                                <button type="button" class="crm-cell crm-cell--layout" :class="isSel(si, fl.floor, f.i) && 'is-selected'" :style="`--c:${color(f.code)}`"
                                                        @click="toggle(si, fl.floor, f.i)" :title="`кв. ${f.n} · ${f.code}`">
                                                    <span x-text="f.n"></span><small x-text="f.code"></small>
                                                </button>
                                            </template>
                                            <em class="crm-muted crm-no-flats" x-show="!fl.flats.length">нет квартир</em>
                                        </div>
                                    </template>
                                </div>
                                <div class="crm-copy-floor">
                                    этажи <input class="form-control form-control-sm" placeholder="3–9" x-model="copyTo">
                                    как этаж <input class="form-control form-control-sm crm-num-in" placeholder="2" x-model="copyFrom">
                                    <button type="button" class="btn btn-light btn-sm" @click="copyFloor(si)">Применить</button>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="crm-section-add" @click="addSection(house)">+ подъезд</button>
                    </div>

                    <p class="crm-muted mt-2">Выделите квартиры (клик по квартире или по номеру этажа) — и смените им планировку. «Этажи … как этаж …» копирует схему этажа.
                        <button type="button" class="btn btn-link btn-sm p-0 ms-2" @click="removeObject()">Удалить этот объект из концепта</button></p>
                </div>

                <div class="crm-selbar" x-cloak x-show="selected.length" x-transition>
                    <span x-text="`Выделено квартир: ${selected.length}`"></span>
                    <select class="form-select form-select-sm" @change="applyLayout($event.target.value); $event.target.value = ''">
                        <option value="">Сменить планировку на…</option>
                        <template x-for="code in layoutCodes()" :key="code"><option :value="code" x-text="code"></option></template>
                    </select>
                    <button type="button" class="btn btn-light btn-sm" @click="removeFlats()">Убрать квартиры</button>
                    <button type="button" class="btn btn-light btn-sm" @click="selected = []">Снять выделение</button>
                </div>
                <div class="crm-toast" x-cloak x-show="toast" x-transition x-text="toast"></div>
            </div>
        </template>
    </div>
@endsection
