@extends('layouts.app', ['title' => "Концепт — {$h['name']}, подъезд $section", 'bodyClass' => 'object-page'])

@section('content')
    @php
        $C = \App\Support\ConceptData::class;
        $n = fn ($v, $d = 0) => number_format((float) $v, $d, ',', ' ');
        $sprite = asset('verstka/img/svg/sprite.svg');
        $flats = collect($data['flats'])->flatten(1)->map(fn ($f) => \Illuminate\Support\Arr::except($f, ['rooms']) + ['url' => route('concept.flat', [$house, $f['n']])])->values();
        $works = collect($C::WORKS)->map(fn ($w, $code) => ['code' => $code, 'name' => $w['name'], 'basis' => $w['basis'], 'color' => $w['color']])->values();
        $brigadeList = collect($C::BRIGADES)->map(fn ($b, $id) => ['id' => $id, 'name' => $b['name']])->values();
        $statusNames = ['todo' => 'не начато', 'work' => 'в работе', 'done' => 'сделано', 'accepted' => 'принято'];
    @endphp
    @include('concept._page', ['crumbs' => [$C::OBJECT => route('concept'), $h['name'] => route('concept.house', $house), "Подъезд $section" => route('concept.section', [$house, $section])],
        'title' => "Подъезд $section", 'subtitle' => $C::OBJECT . " · {$h['name']} · {$h['floors']} этажей"])

    <div class="container-fluid">
        @include('concept._scope', ['house' => $house, 'section' => $section])

        <div class="crm-tabs-row crm-concept-tabs">
            <a class="toggle-block__btn btn btn-outline-light {{ $view === 'chess' ? 'active' : '' }}" href="{{ route('concept.section', [$house, $section]) }}">
                <svg class="icon icon-tile"><use xlink:href="{{ $sprite }}#tile"></use></svg><span>Квартиры — шахматка</span></a>
            <a class="toggle-block__btn btn btn-outline-light {{ $view === 'mop' ? 'active' : '' }}" href="{{ route('concept.section', [$house, $section, 'mop']) }}">
                <svg class="icon icon-list"><use xlink:href="{{ $sprite }}#list"></use></svg><span>МОП ({{ count($data['mop']) }})</span></a>
        </div>

        @if ($view === 'mop')
            <div class="main-table crm-concept-table crm-mop">
                <table>
                    <thead><tr><th>Помещение МОП</th><th>Этаж</th><th>Стены, м²</th><th>Пол, м²</th>@foreach ($C::WORKS as $w)<th>{{ $w['name'] }}</th>@endforeach</tr></thead>
                    <tbody>
                    @foreach ($data['mop'] as $m)
                        <tr>
                            <td>{{ $m['name'] }}</td><td>{{ $m['floor'] ?: 'подвал' }}</td><td>{{ $n($m['walls'], 1) }}</td><td>{{ $n($m['floor_m2'], 1) }}</td>
                            @foreach ($m['works'] as $code => $w)
                                <td><span class="crm-st st-{{ $w['status'] }}">{{ $statusNames[$w['status']] }}</span></td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <p class="crm-muted mt-2">МОП — места общего пользования: холлы, лестницы, тамбур, техподполье. Их отделывают после квартир, поэтому они отстают на пару этажей — это видно по статусам.</p>
        @else
            <div x-data="chess(@js($flats), @js($works), @js($brigadeList), @js("lps-concept-chess:$house:$section"))">
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
                        <div class="crm-chess-section">
                            <template x-for="floor in floors()" :key="floor">
                                <div class="crm-chess-row" :class="isSelected(floor) && 'selected'">
                                    <button type="button" class="crm-chess-floor" @click="toggleFloor(floor)" :title="'Выделить этаж ' + floor" x-text="floor"></button>
                                    <template x-for="f in flatsOn(floor)" :key="f.n">
                                        <button type="button" class="crm-cell" :class="cellClass(f)" :style="cellStyle(f)" @click="openFlat(f)" :title="cellTitle(f)">
                                            <span x-text="f.n"></span><small x-text="f.type"></small>
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="crm-chess-hint">Нажмите на квартиру — откроется карточка, изменения сохраняются кнопкой «Сохранить». На номер этажа — выделится весь этаж: назначение бригады и «сделано» сохраняются сразу.</div>
                </div>

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
                <div class="crm-toast" x-cloak x-show="toast" x-transition x-text="toast"></div>

                <div class="crm-drawer-bg" x-cloak x-show="open" x-transition.opacity @click="closeFlat()"></div>
                <div class="crm-drawer" x-cloak x-show="open" x-transition>
                    <template x-if="open">
                        <div>
                            <button type="button" class="crm-drawer__close" @click="closeFlat()">✕</button>
                            <h3 x-text="`Квартира ${open.n}`"></h3>
                            <p class="crm-muted" x-text="`Этаж ${open.floor} · планировка ${open.type}`"></p>
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
                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-accent flex-fill" @click="saveFlat()" :disabled="!flatDirty()" x-text="flatDirty() ? 'Сохранить' : 'Сохранено'"></button>
                                <button type="button" class="btn btn-light" x-show="flatDirty()" @click="restoreFlat()">Отменить</button>
                            </div>
                            <a class="btn btn-light w-100 mt-2" :href="open.url" @click="if (flatDirty() && !confirm('Изменения не сохранены. Перейти без сохранения?')) $event.preventDefault()">Открыть квартиру: помещения и замеры →</a>
                        </div>
                    </template>
                </div>
            </div>

            <script>
                document.addEventListener('alpine:init', () => {
                    const palette = { 1: '#F4B13E', 2: '#E8913A', 3: '#467CF4', 4: '#1DBA6F', 5: '#9A52DF' };
                    const names = { todo: 'не начато', work: 'в работе', done: 'сделано', accepted: 'принято' };
                    Alpine.data('chess', (flats, works, brigades, key) => ({
                        flats, works, brigades, layer: 'plaster', open: null, selected: [], toast: '', snap: '',
                        // Статусы и бригады сохраняются: в концепте — в этом браузере, в рабочей версии — в базу
                        init() {
                            try {
                                const saved = JSON.parse(localStorage.getItem(key) || '{}');
                                this.flats.forEach(f => { if (saved[f.n]) f.works = saved[f.n]; });
                            } catch (e) {}
                        },
                        persist() {
                            const out = {}; this.flats.forEach(f => out[f.n] = f.works);
                            try { localStorage.setItem(key, JSON.stringify(out)); return true; } catch (e) { return false; }
                        },
                        say(t) { this.toast = t; clearTimeout(this._t); this._t = setTimeout(() => this.toast = '', 2500); },
                        openFlat(f) { this.open = f; this.snap = JSON.stringify(f.works); },
                        flatDirty() { return this.open && JSON.stringify(this.open.works) !== this.snap; },
                        restoreFlat() { if (this.open) this.open.works = JSON.parse(this.snap); },
                        saveFlat() { if (this.persist()) { this.snap = JSON.stringify(this.open.works); this.say(`Квартира ${this.open.n} сохранена`); this.open = null; } },
                        closeFlat() {
                            if (this.flatDirty()) { if (!confirm('Изменения в квартире не сохранены. Закрыть без сохранения?')) return; this.restoreFlat(); }
                            this.open = null;
                        },
                        floors() { return [...new Set(this.flats.map(f => f.floor))].sort((a, b) => b - a); },
                        flatsOn(fl) { return this.flats.filter(f => f.floor === fl); },
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
                            return `Кв. ${f.n}, ${f.type}` + (w ? ` — ${w.name}: ${names[f.works[w.code].status]}` : '') + (f.problem ? ` — ${f.problem}` : '');
                        },
                        summary() {
                            const w = this.works.find(x => x.code === this.layer);
                            let total = 0, done = 0, work = 0;
                            this.flats.forEach(f => { const m = f[w.basis], s = f.works[w.code].status; total += m; if (s === 'done' || s === 'accepted') done += m; else if (s === 'work') work += m; });
                            const fmt = v => Math.round(v).toLocaleString('ru-RU');
                            return `${w.name} в квартирах: сделано ${fmt(done)} из ${fmt(total)} м² (${Math.round(done / total * 100)}%), в работе ${fmt(work)} м²`;
                        },
                        isSelected(fl) { return this.selected.includes(fl); },
                        toggleFloor(fl) { this.selected = this.isSelected(fl) ? this.selected.filter(x => x !== fl) : [...this.selected, fl]; },
                        selectedFlats() { return this.flats.filter(f => this.isSelected(f.floor)); },
                        setStatus(st) { const n = this.selectedFlats().length; this.selectedFlats().forEach(f => f.works[this.layer].status = st); this.selected = []; this.persist(); this.say(`Сохранено: ${n} кв. — ${names[st]}`); },
                        assign(id) { if (!id) return; const n = this.selectedFlats().length; this.selectedFlats().forEach(f => { f.works[this.layer].brigade = +id; if (f.works[this.layer].status === 'todo') f.works[this.layer].status = 'work'; }); this.selected = []; this.persist(); this.say(`Сохранено: бригада назначена на ${n} кв.`); },
                    }));
                });
            </script>
        @endif
    </div>
@endsection
