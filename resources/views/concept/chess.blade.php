@extends('layouts.app', ['title' => 'Шахматка — концепт', 'bodyClass' => 'object-page'])

@section('content')
    @include('concept._head')

    @php
        $flats = collect($chess)->flatMap(fn ($floors) => collect($floors)->flatten(1))->values();
        $works = collect(\App\Support\ConceptData::WORKS)->map(fn ($w, $code) => ['code' => $code, 'name' => $w['name'], 'basis' => $w['basis'], 'color' => $w['color']])->values();
        $brigadeList = collect(\App\Support\ConceptData::BRIGADES)->map(fn ($b, $id) => ['id' => $id, 'name' => $b['name'], 'work' => $b['work']])->values();
    @endphp

    <div class="container-fluid" x-data="chess(@js($flats), @js($works), @js($brigadeList))">
        <div class="crm-panel crm-chess-panel">
            <div class="crm-chess-toolbar">
                <div class="crm-layers">
                    <span class="crm-label">Слой:</span>
                    <button type="button" class="crm-layer" :class="layer === 'measure' && 'active'" @click="layer = 'measure'">Замер</button>
                    <template x-for="w in works" :key="w.code">
                        <button type="button" class="crm-layer" :class="layer === w.code && 'active'" @click="layer = w.code" :style="layer === w.code ? `--c:${w.color}` : ''" x-text="w.name"></button>
                    </template>
                    <button type="button" class="crm-layer" :class="layer === 'brigades' && 'active'" @click="layer = 'brigades'">Бригады</button>
                </div>
                <div class="crm-chess-summary" x-show="layer !== 'measure' && layer !== 'brigades'" x-text="summary()"></div>
            </div>

            <div class="crm-legend" x-show="layer !== 'brigades'">
                <template x-if="layer === 'measure'"><span><i class="st-accepted"></i>замерено <i class="st-todo"></i>нет замера</span></template>
                <template x-if="layer !== 'measure'"><span><i class="st-todo"></i>не начато <i class="st-work"></i>в работе <i class="st-done"></i>сделано <i class="st-accepted"></i>принято <i class="st-problem"></i>проблема</span></template>
            </div>
            <div class="crm-legend" x-show="layer === 'brigades'">
                <template x-for="b in brigades" :key="b.id"><span><i :style="`background:${brigadeColor(b.id)}`"></i><span x-text="b.name"></span></span></template>
                <span><i class="st-todo"></i>без бригады</span>
            </div>

            <div class="crm-chess">
                <template x-for="section in sections" :key="section">
                    <div class="crm-chess-section">
                        <div class="crm-chess-title" x-text="`Подъезд ${section}`"></div>
                        <template x-for="floor in floors(section)" :key="floor">
                            <div class="crm-chess-row" :class="isSelected(section, floor) && 'selected'">
                                <button type="button" class="crm-chess-floor" @click="toggleFloor(section, floor)" :title="'Выделить этаж ' + floor" x-text="floor"></button>
                                <template x-for="f in flatsOn(section, floor)" :key="f.n">
                                    <button type="button" class="crm-cell" :class="cellClass(f)" :style="cellStyle(f)" @click="open = f" :title="cellTitle(f)">
                                        <span x-text="f.n"></span>
                                        <small x-text="f.type"></small>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="crm-chess-hint">Нажмите на номер этажа, чтобы выделить его целиком, — и назначьте бригаду или отметьте работу сразу по всем квартирам.</div>
        </div>

        {{-- Действия над выделенными этажами --}}
        <div class="crm-selbar" x-cloak x-show="selected.length" x-transition>
            <span x-text="`Выделено этажей: ${selected.length}, квартир: ${selectedFlats().length}`"></span>
            <template x-if="layer !== 'measure' && layer !== 'brigades'">
                <span class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-light btn-sm" @click="setStatus('work')">В работу</button>
                    <button type="button" class="btn btn-accent btn-sm" @click="setStatus('done')">Отметить «сделано»</button>
                    <select class="form-select form-select-sm" @change="assign($event.target.value); $event.target.value = ''">
                        <option value="">Назначить бригаду…</option>
                        <template x-for="b in brigades" :key="b.id"><option :value="b.id" x-text="b.name"></option></template>
                    </select>
                </span>
            </template>
            <template x-if="layer === 'measure' || layer === 'brigades'"><span class="crm-muted">Выберите слой вида работ, чтобы менять статус или бригаду</span></template>
            <button type="button" class="btn btn-light btn-sm" @click="selected = []">Снять выделение</button>
        </div>

        {{-- Карточка квартиры --}}
        <div class="crm-drawer-bg" x-cloak x-show="open" x-transition.opacity @click="open = null"></div>
        <div class="crm-drawer" x-cloak x-show="open" x-transition>
            <template x-if="open">
                <div>
                    <button type="button" class="crm-drawer__close" @click="open = null">✕</button>
                    <h3 x-text="`Квартира ${open.n}`"></h3>
                    <p class="crm-muted" x-text="`Подъезд ${open.section}, этаж ${open.floor} · планировка ${open.type} · ${open.rooms} помещений`"></p>
                    <div class="crm-drawer__facts">
                        <div><span>Стены</span><b x-text="open.walls.toLocaleString('ru-RU') + ' м²'"></b></div>
                        <div><span>Пол</span><b x-text="open.floor_m2.toLocaleString('ru-RU') + ' м²'"></b></div>
                        <div><span>Замер</span><b x-text="open.measured ? 'есть' : 'нет'"></b></div>
                    </div>
                    <div class="crm-warning" x-show="open.problem" x-text="'⚠ ' + open.problem"></div>
                    <h4 class="crm-h4">Работы</h4>
                    <template x-for="w in works" :key="w.code">
                        <div class="crm-drawer__work">
                            <div><b x-text="w.name"></b><small x-text="(open[w.basis]).toLocaleString('ru-RU') + ' м²'"></small></div>
                            <select class="form-select form-select-sm" x-model="open.works[w.code].status">
                                <option value="todo">не начато</option><option value="work">в работе</option>
                                <option value="done">сделано</option><option value="accepted">принято</option>
                            </select>
                            <select class="form-select form-select-sm" x-model.number="open.works[w.code].brigade">
                                <option :value="null">без бригады</option>
                                <template x-for="b in brigades" :key="b.id"><option :value="b.id" x-text="b.name" :selected="open.works[w.code].brigade === b.id"></option></template>
                            </select>
                        </div>
                    </template>
                    <p class="crm-muted mt-3">В рабочей версии здесь же — помещения с замерами, фото с объекта и история: кто и когда отметил.</p>
                </div>
            </template>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            const palette = { 1: '#F4B13E', 2: '#E8913A', 3: '#467CF4', 4: '#1DBA6F' };
            Alpine.data('chess', (flats, works, brigades) => ({
                flats, works, brigades, layer: 'plaster', open: null, selected: [],
                get sections() { return [...new Set(this.flats.map(f => f.section))]; },
                floors(s) { return [...new Set(this.flats.filter(f => f.section === s).map(f => f.floor))].sort((a, b) => b - a); },
                flatsOn(s, fl) { return this.flats.filter(f => f.section === s && f.floor === fl); },
                brigadeColor(id) { return palette[id] || '#c9ced2'; },
                status(f) { return this.layer === 'measure' ? (f.measured ? 'accepted' : 'todo') : f.works[this.layer].status; },
                cellClass(f) {
                    if (this.layer === 'brigades') return 'st-brigade';
                    return 'st-' + this.status(f) + (f.problem && this.layer !== 'measure' ? ' has-problem' : '');
                },
                cellStyle(f) {
                    if (this.layer !== 'brigades') return '';
                    const active = Object.values(f.works).find(w => w.status === 'work' && w.brigade);
                    return active ? `background:${this.brigadeColor(active.brigade)};color:#fff` : '';
                },
                cellTitle(f) {
                    const w = this.works.find(x => x.code === this.layer);
                    return `Кв. ${f.n}, ${f.type}` + (w ? ` — ${w.name}: ${({todo:'не начато',work:'в работе',done:'сделано',accepted:'принято'})[f.works[w.code].status]}` : '') + (f.problem ? ` — ${f.problem}` : '');
                },
                summary() {
                    const w = this.works.find(x => x.code === this.layer);
                    let total = 0, done = 0, work = 0;
                    this.flats.forEach(f => { const m = f[w.basis], s = f.works[w.code].status; total += m; if (s === 'done' || s === 'accepted') done += m; else if (s === 'work') work += m; });
                    const fmt = v => Math.round(v).toLocaleString('ru-RU');
                    return `${w.name}: сделано ${fmt(done)} из ${fmt(total)} м² (${Math.round(done / total * 100)}%), в работе ${fmt(work)} м²`;
                },
                key(s, fl) { return s + ':' + fl; },
                isSelected(s, fl) { return this.selected.includes(this.key(s, fl)); },
                toggleFloor(s, fl) { const k = this.key(s, fl); this.selected = this.isSelected(s, fl) ? this.selected.filter(x => x !== k) : [...this.selected, k]; },
                selectedFlats() { return this.flats.filter(f => this.isSelected(f.section, f.floor)); },
                setStatus(st) { this.selectedFlats().forEach(f => f.works[this.layer].status = st); this.selected = []; },
                assign(id) { if (!id) return; this.selectedFlats().forEach(f => { f.works[this.layer].brigade = +id; if (f.works[this.layer].status === 'todo') f.works[this.layer].status = 'work'; }); this.selected = []; },
            }));
        });
    </script>
@endsection
