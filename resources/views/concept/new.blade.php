@extends('layouts.app', ['title' => 'Новый объект — мастер (концепт)', 'bodyClass' => 'object-page'])

@section('content')
    @include('concept._page', ['crumbs' => ['Новый объект — мастер' => route('concept.new')], 'title' => 'Новый объект',
        'subtitle' => 'Дом собирается из типовых планировок и схем подъездов — квартиры по одной вводить не нужно'])

    <script src="{{ asset('js/concept-builder.js') }}?v=2"></script>

    <div class="container-fluid" x-data="conceptWizard(@js(route('concept.built')))">
        <ol class="crm-steps-bar">
            <template x-for="(name, k) in steps" :key="k">
                <li :class="{ active: step === k + 1, done: step > k + 1 }" @click="step = k + 1"><b x-text="k + 1"></b><span x-text="name"></span></li>
            </template>
        </ol>

        <div class="crm-wizard">
            <div class="crm-wizard__main">

                {{-- Шаг 1. Объект --}}
                <div class="crm-card" x-show="step === 1">
                    <h3>Объект</h3>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="crm-label">Название</label><input class="form-control" x-model="obj.name"></div>
                        <div class="col-md-6"><label class="crm-label">Адрес</label><input class="form-control" x-model="obj.address"></div>
                        <div class="col-md-6"><label class="crm-label">Заказчик</label><input class="form-control" x-model="obj.customer" placeholder="Застройщик"></div>
                        <div class="col-md-6"><label class="crm-label">ИНН заказчика</label>
                            <input class="form-control" inputmode="numeric" maxlength="12" placeholder="10 цифр — компания, 12 — ИП"
                                   x-model="obj.inn" @input="obj.inn = $event.target.value.replace(/\D/g, '')">
                            <small class="crm-muted" x-show="obj.inn && ![10, 12].includes(obj.inn.length)" x-cloak>ИНН — 10 цифр у компании или 12 у ИП</small></div>
                    </div>
                    <p class="crm-muted mt-3">Дальше: какие планировки квартир есть в проекте, затем из них соберём дома и подъезды.</p>
                </div>

                {{-- Шаг 2. Планировки --}}
                <div class="crm-card" x-show="step === 2">
                    <h3>Типовые планировки</h3>
                    <p class="crm-muted">Квартира-образец: помещения с размерами. Заводится один раз и используется во всех квартирах этого типа.
                        Исправили образец — исправились все такие квартиры.</p>
                    <div class="crm-layouts">
                        <div class="crm-layouts__list">
                            <template x-for="code in layoutCodes()" :key="code">
                                <button type="button" class="crm-layouts__item" :class="editLayout === code && 'active'" @click="editLayout = code">
                                    <i :style="`background:${color(code)}`"></i>
                                    <span><b x-text="code"></b><small x-text="`${obj.layouts[code].rooms.length} пом. · ${fmt(areas(code).floor, 1)} м² пола`"></small></span>
                                    <em x-show="ex().totals.byLayout[code]" x-text="ex().totals.byLayout[code] + ' кв.'"></em>
                                </button>
                            </template>
                            <button type="button" class="btn btn-light btn-sm mt-2" @click="addLayout()">+ Новая планировка (копия выбранной)</button>
                        </div>
                        <div class="crm-layouts__edit" x-show="obj.layouts[editLayout]">
                            <template x-if="obj.layouts[editLayout]">
                                <div>
                                    <div class="d-flex gap-2 align-items-end flex-wrap mb-2">
                                        <div><label class="crm-label">Название планировки</label>
                                            <input class="form-control" :value="editLayout" @change="renameLayout(editLayout, $event.target.value)" style="width: 180px"></div>
                                        <div class="crm-muted" x-text="`Пол ${fmt(areas(editLayout).floor, 1)} м² · стены ${fmt(areas(editLayout).walls, 1)} м² (высота 2,7 м)`"></div>
                                        <button type="button" class="btn btn-light btn-sm ms-auto" @click="removeLayout(editLayout)" :disabled="ex().totals.byLayout[editLayout] || layoutCodes().length < 2"
                                                :title="ex().totals.byLayout[editLayout] ? 'Планировка используется в квартирах' : 'Удалить планировку'">Удалить</button>
                                    </div>
                                    <table class="crm-table">
                                        <thead><tr><th>Помещение</th><th>A, м</th><th>B, м</th><th>Дверей</th><th>Окон</th><th>Пол, м²</th><th>Стены, м²</th><th></th></tr></thead>
                                        <tbody>
                                        <template x-for="(r, k) in obj.layouts[editLayout].rooms" :key="k">
                                            <tr>
                                                <td><input class="form-control form-control-sm" x-model="r.name"></td>
                                                <td><input class="form-control form-control-sm crm-num-in" inputmode="decimal" x-model="r.a"></td>
                                                <td><input class="form-control form-control-sm crm-num-in" inputmode="decimal" x-model="r.b"></td>
                                                <td><input class="form-control form-control-sm crm-num-in" inputmode="numeric" x-model="r.doors"></td>
                                                <td><input class="form-control form-control-sm crm-num-in" inputmode="numeric" x-model="r.windows"></td>
                                                <td x-text="fmt(roomAreas(r).floor, 2)"></td>
                                                <td x-text="fmt(roomAreas(r).walls, 2)"></td>
                                                <td><button type="button" class="btn btn-light btn-sm" @click="obj.layouts[editLayout].rooms.splice(k, 1)">✕</button></td>
                                            </tr>
                                        </template>
                                        </tbody>
                                    </table>
                                    <button type="button" class="btn btn-light btn-sm" @click="obj.layouts[editLayout].rooms.push({ name: 'Помещение', a: 3, b: 3, doors: 1, windows: 0 })">+ помещение</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Шаг 3. Дома и подъезды --}}
                <div x-show="step === 3">
                    <template x-for="(h, hi) in obj.houses" :key="hi">
                        <div class="crm-card crm-house">
                            <div class="crm-house__head">
                                <input class="form-control crm-house__name" x-model="h.name">
                                <span class="crm-muted" x-text="`${h.sections.length} подъезд(а) · ${ex().houses[hi]?.sectionsX.reduce((k, s) => k + sectionFlats(s), 0) ?? 0} кв.`"></span>
                                <button type="button" class="btn btn-light btn-sm ms-auto" @click="copyHouse(hi)">Копировать дом</button>
                                <button type="button" class="btn btn-light btn-sm" @click="removeHouse(hi)" :disabled="obj.houses.length < 2">Удалить</button>
                            </div>
                            <div class="crm-sections">
                                <template x-for="(s, si) in h.sections" :key="si">
                                    <div class="crm-section-card">
                                        <div class="crm-section-card__head">
                                            <b x-text="`Подъезд ${si + 1}`"></b>
                                            <label class="crm-inline-field">этажей <input class="form-control form-control-sm crm-num-in" inputmode="numeric" :value="s.floors" @change="setFloors(s, $event.target.value)"></label>
                                            <span class="crm-muted ms-auto" x-text="`${ex().houses[hi]?.sectionsX[si] ? sectionFlats(ex().houses[hi].sectionsX[si]) : 0} кв.`"></span>
                                        </div>
                                        <template x-for="(r, ri) in s.ranges" :key="ri">
                                            <div class="crm-range">
                                                <span class="crm-range__floors">этажи
                                                    <input class="form-control form-control-sm crm-num-in" inputmode="numeric" x-model="r.from" @change="normalize(s)"> –
                                                    <input class="form-control form-control-sm crm-num-in" inputmode="numeric" x-model="r.to" @change="normalize(s)">
                                                </span>
                                                <span class="crm-range__scheme">
                                                    <template x-for="(code, ci) in r.scheme" :key="ci">
                                                        <span class="crm-chip" :style="`--c:${color(code)}`"><span x-text="code"></span><button type="button" @click="r.scheme.splice(ci, 1); normalize(s)">×</button></span>
                                                    </template>
                                                    <em class="crm-muted" x-show="!r.scheme.length">квартир нет (коммерция, колясочная)</em>
                                                    <select class="form-select form-select-sm crm-add-chip" @change="if ($event.target.value) { r.scheme.push($event.target.value); normalize(s) } $event.target.value = ''">
                                                        <option value="">+ квартира</option>
                                                        <template x-for="code in layoutCodes()" :key="code"><option :value="code" x-text="code"></option></template>
                                                    </select>
                                                </span>
                                            </div>
                                        </template>
                                        <button type="button" class="btn btn-light btn-sm" @click="addRange(s)">+ диапазон этажей с другой схемой</button>
                                        <div class="crm-mop-toggles">
                                            <span class="crm-label">МОП:</span>
                                            <label><input type="checkbox" class="form-check-input" x-model="s.mop.hall"> холл на этаже</label>
                                            <label><input type="checkbox" class="form-check-input" x-model="s.mop.stairs"> лестница</label>
                                            <label><input type="checkbox" class="form-check-input" x-model="s.mop.vestibule"> тамбур</label>
                                            <label><input type="checkbox" class="form-check-input" x-model="s.mop.basement"> техподполье</label>
                                            <label><input type="checkbox" class="form-check-input" x-model="s.mop.roof"> выход на кровлю</label>
                                        </div>
                                        <div class="crm-section-card__foot">
                                            <button type="button" class="btn btn-light btn-sm" @click="copySection(hi, si)">Копировать подъезд</button>
                                            <button type="button" class="btn btn-light btn-sm" @click="removeSection(hi, si)" :disabled="h.sections.length < 2">Удалить</button>
                                        </div>
                                    </div>
                                </template>
                                <button type="button" class="crm-section-add" @click="addSection(hi)">+ подъезд</button>
                            </div>
                        </div>
                    </template>
                    <button type="button" class="btn btn-light" @click="addHouse()">+ дом</button>
                </div>

                {{-- Шаг 4. Проверка --}}
                <div x-show="step === 4">
                    <div class="crm-card">
                        <h3 x-text="obj.name"></h3>
                        <p class="crm-muted" x-text="[obj.address, obj.customer, obj.inn && 'ИНН ' + obj.inn].filter(Boolean).join(' · ')"></p>
                        <div class="crm-legend">
                            <template x-for="code in layoutCodes()" :key="code">
                                <span><i :style="`background:${color(code)}`"></i><span x-text="`${code} — ${ex().totals.byLayout[code] || 0} кв.`"></span></span>
                            </template>
                        </div>
                        <template x-for="(h, hi) in ex().houses" :key="hi">
                            <div class="mt-3">
                                <h4 class="crm-h4" x-text="h.name"></h4>
                                <div class="crm-mini-chess-row">
                                    <template x-for="(s, si) in h.sectionsX" :key="si">
                                        <div class="crm-mini-chess">
                                            <div class="crm-label" x-text="`Подъезд ${si + 1} · ${s.floors} эт. · ${sectionFlats(s)} кв. · МОП ${s.mop.length}`"></div>
                                            <template x-for="fl in s.floorsList" :key="fl.floor">
                                                <div class="crm-mini-chess__row">
                                                    <span x-text="fl.floor"></span>
                                                    <template x-for="f in fl.flats" :key="f.n"><i :style="`background:${color(f.code)}`" :title="`кв. ${f.n} · ${f.code}`" x-text="f.n"></i></template>
                                                    <em x-show="!fl.flats.length">—</em>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="crm-wizard__nav">
                    <span class="crm-wizard__mini" x-text="`${fmt(ex().totals.flats)} кв · ${fmt(ex().totals.mop)} МОП · ${fmt(ex().totals.walls)} м² стен`"></span>
                    <button type="button" class="btn btn-light" @click="prev()" x-show="step > 1">← Назад</button>
                    <button type="button" class="btn btn-accent" @click="next()" x-show="step < 4">Дальше →</button>
                    <button type="button" class="btn btn-accent" @click="create()" x-show="step === 4">Создать объект</button>
                </div>
            </div>

            {{-- Итоги — пересчитываются на каждом шаге --}}
            <div class="crm-wizard__side">
                <div class="crm-card crm-sticky">
                    <h3>Итого</h3>
                    <p class="crm-kv"><span>Домов / подъездов</span><b x-text="`${ex().totals.houses} / ${ex().totals.sections}`"></b></p>
                    <p class="crm-kv"><span>Квартир</span><b data-flash="w-flats" x-text="fmt(ex().totals.flats)"></b></p>
                    <p class="crm-kv"><span>МОП</span><b data-flash="w-mop" x-text="fmt(ex().totals.mop)"></b></p>
                    <p class="crm-kv"><span>Помещений в квартирах</span><b data-flash="w-rooms" x-text="fmt(ex().totals.rooms)"></b></p>
                    <p class="crm-kv"><span>Стены</span><b data-flash="w-walls" x-text="fmt(ex().totals.walls) + ' м²'"></b></p>
                    <p class="crm-kv"><span>Пол</span><b data-flash="w-floor" x-text="fmt(ex().totals.floor) + ' м²'"></b></p>
                    <p class="crm-muted mt-2">Эти площади сразу пойдут в смету и в объёмы работ по видам.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
