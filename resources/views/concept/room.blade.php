@extends('layouts.form', ['title' => "Концепт — {$room['name']}, кв. {$flat['n']}"])

@section('content')
    @php
        $C = \App\Support\ConceptData::class;
        $sprite = asset('verstka/img/svg/sprite.svg');
        $statusNames = ['todo' => 'не начато', 'work' => 'в работе', 'done' => 'сделано', 'accepted' => 'принято'];
        $rooms = $flat['rooms'];
        // как на странице квартиры: в квартире «в работе» первая половина помещений уже сделана
        $st = fn (string $s) => $s === 'work' ? ($i < ceil(count($rooms) / 2) ? 'done' : 'work') : $s;
        $works = collect($flat['works'])->map(fn ($w, $code) => ['code' => $code, 'name' => $C::WORKS[$code]['name'], 'color' => $C::WORKS[$code]['color'],
            'basis' => $C::WORKS[$code]['basis'], 'status' => $st($w['status']),
            'brigade' => $w['brigade'] ? $C::BRIGADES[$w['brigade']]['name'] : null])->values();
        $flatUrl = route('concept.flat', [$house, $flat['n']]);
        $prev = $i > 0 ? $rooms[$i - 1] : null;
        $next = $rooms[$i + 1] ?? null;
        // история: замер и отметки прораба — даты условные, от старта объекта
        $history = [['14 окт, 10:20', 'Замер помещения', 'замерщик Алексей К., лазерный дальномер']];
        $day = 15;
        foreach ($works as $w) {
            if ($w['status'] === 'todo') continue;
            $history[] = [($day += 2) . ' окт, 18:05', $w['name'] . ' — в работе', $w['brigade'] ?? 'без бригады'];
            if (in_array($w['status'], ['done', 'accepted'], true)) $history[] = [($day += 3) . ' окт, 17:40', $w['name'] . ' — ' . $statusNames[$w['status']], 'прораб Дмитрий В.'];
        }
    @endphp

    <div class="sCreateObject" x-data="roomMeasure(@js(['a' => $room['a'], 'b' => $room['b'], 'h' => $C::HEIGHT, 'openings' => $room['openings'], 'key' => "lps-concept-room:$house:{$flat['n']}:$i"]))">
        <div class="sCreateObject__body">
            <div class="sCreateObject__left-side">
                <p class="crm-crumb">
                    <a href="{{ route('concept') }}">{{ $C::OBJECT }}</a> ›
                    <a href="{{ route('concept.house', $house) }}">{{ $h['name'] }}</a> ›
                    <a href="{{ route('concept.section', [$house, $flat['section']]) }}">подъезд {{ $flat['section'] }}</a> ›
                    <a href="{{ $flatUrl }}">кв. {{ $flat['n'] }}</a>
                    <span class="crm-concept-badge ms-2">концепт</span>
                </p>
                <h2>{{ $room['name'] }}</h2>

                @unless ($flat['measured'])
                    <div class="crm-warning">Замер квартиры не сделан — нет доступа. Ниже — размеры по проекту, после замера заменятся фактическими.</div>
                @endunless

                <div class="plan-block mt-3">
                    <div class="crm-plan">
                        <svg viewBox="0 0 240 200">
                            <rect :x="planX()" :y="planY()" :width="planW()" :height="planH()" fill="#d9d9d9" stroke="#8b9298" stroke-width="4"/>
                            {{-- окна — синим на верхней стене, двери — разрывом на нижней (template внутри svg Alpine не повторяет — рисуем путём) --}}
                            <path :d="marks('ОК')" stroke="#467cf4" stroke-width="7" fill="none"/>
                            <path :d="marks('ДВ')" stroke="#fff" stroke-width="7" fill="none"/>
                            <text :x="planX() + planW() / 2 - 6" :y="planY() - 8" class="lbl">A</text>
                            <text :x="planX() - 18" :y="planY() + planH() / 2 + 5" class="lbl">B</text>
                        </svg>
                        <div class="crm-plan-legend"><span><i class="w"></i>окно</span><span><i class="d"></i>дверь</span></div>
                    </div>
                    <div class="plan-block__info">
                        <p class="row"><span class="col">Площадь пола</span><span class="col-auto" data-flash="r-floor" x-text="fmt(floor())"></span></p>
                        <p class="row"><span class="col">Площадь стен</span><span class="col-auto" data-flash="r-walls" x-text="fmt(walls())"></span></p>
                        <p class="row"><span class="col">Площадь потолка</span><span class="col-auto" data-flash="r-ceiling" x-text="fmt(floor())"></span></p>
                        <p class="row"><span class="col">Периметр</span><span class="col-auto" data-flash="r-per" x-text="fmt(perimeter())"></span></p>
                        <p class="row"><span class="col">Проёмы</span><span class="col-auto" data-flash="r-op" x-text="fmt(openingsArea())"></span></p>
                        <p class="row"><span class="col">Периметр без дверей (плинтус)</span><span class="col-auto" data-flash="r-net" x-text="fmt(perimeterNet())"></span></p>
                        <p class="crm-hint">Поменяйте размер — площади пересчитаются. «Сохранить замер» внизу — и новые площади уйдут в квартиру.</p>
                    </div>
                </div>

                <h4 class="crm-h4">Замер
                    <span class="crm-dirty" x-show="dirty()" x-cloak>изменено — не сохранено</span>
                    <span class="crm-saved" x-show="!dirty() && savedAt" x-cloak x-text="'сохранён ' + savedLabel()"></span>
                </h4>
                <div class="row g-3">
                    <div class="col-4"><label class="crm-label">Длина A, м</label><input class="form-control" inputmode="decimal" x-model="a"></div>
                    <div class="col-4"><label class="crm-label">Ширина B, м</label><input class="form-control" inputmode="decimal" x-model="b"></div>
                    <div class="col-4"><label class="crm-label">Высота H, м</label><input class="form-control" inputmode="decimal" x-model="h"></div>
                </div>

                <h4 class="crm-h4">Проёмы</h4>
                <table class="crm-table">
                    <thead><tr><th>Тип</th><th>Что</th><th>Ширина, м</th><th>Высота, м</th><th>Площадь, м²</th><th></th></tr></thead>
                    <tbody>
                    <template x-for="(o, k) in openings" :key="k">
                        <tr>
                            <td><span class="crm-tag" x-text="o.type"></span></td>
                            <td><input class="form-control form-control-sm" x-model="o.label"></td>
                            <td><input class="form-control form-control-sm" inputmode="decimal" x-model="o.width"></td>
                            <td><input class="form-control form-control-sm" inputmode="decimal" x-model="o.height"></td>
                            <td x-text="fmt(num(o.width) * num(o.height))"></td>
                            <td><button type="button" class="btn btn-light btn-sm" @click="openings.splice(k, 1)" title="Убрать проём">✕</button></td>
                        </tr>
                    </template>
                    <tr x-show="!openings.length"><td colspan="6" class="crm-muted">Проёмов нет — стены считаются целиком</td></tr>
                    </tbody>
                </table>
                <div class="d-flex gap-2 flex-wrap mb-2">
                    <button type="button" class="btn btn-light btn-sm" @click="addOpening('ДВ')">+ дверь</button>
                    <button type="button" class="btn btn-light btn-sm" @click="addOpening('ОК')">+ окно</button>
                    <button type="button" class="btn btn-light btn-sm" @click="addOpening('ПР')">+ проём</button>
                </div>

                <h4 class="crm-h4">Работы в помещении</h4>
                <table class="crm-table">
                    <thead><tr><th>Вид работ</th><th>Объём, м²</th><th>Статус</th><th>Бригада</th></tr></thead>
                    <tbody>
                    @foreach ($works as $w)
                        <tr>
                            <td><span class="crm-dot" style="--c: {{ $w['color'] }}"></span> {{ $w['name'] }}</td>
                            <td x-text="fmt({{ $w['basis'] === 'walls' ? 'walls()' : 'floor()' }})"></td>
                            <td><span class="crm-st st-{{ $w['status'] }}">{{ $statusNames[$w['status']] }}</span></td>
                            <td class="crm-muted">{{ $w['brigade'] ?? ($w['status'] === 'todo' ? '—' : 'без бригады') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <p class="crm-muted">Объёмы работ берутся из этого замера — те же цифры идут в смету и в задание бригаде.</p>
            </div>

            <div class="sCreateObject__right-side">
                <div class="sCreateObject__row row">
                    <div class="col-12"><div class="sCreateObject__item"><p>Где</p><span>{{ $h['name'] }}, подъезд {{ $flat['section'] }}, этаж {{ $flat['floor'] }}, кв. {{ $flat['n'] }} ({{ $flat['type'] }})</span></div></div>
                    <div class="col-12"><div class="sCreateObject__item"><p>Замер</p>
                        <span x-show="!savedAt">{{ $flat['measured'] ? '14.10.2026, замерщик Алексей К.' : 'не сделан — нет доступа' }}</span>
                        <span x-show="savedAt" x-cloak x-text="'изменён ' + savedLabel() + ' (было по проекту — «Вернуть по проекту»)'"></span>
                    </div></div>
                    @if ($flat['problem'])
                        <div class="col-12"><div class="sCreateObject__item"><p>Проблема</p><span class="text-danger">{{ $flat['problem'] }}</span></div></div>
                    @endif
                    <div class="col-12">
                        <div class="sCreateObject__item">
                            <p>Фото с объекта</p>
                            <div class="crm-photos"><div>📷<small>до работ</small></div><div>📷<small>после штукатурки</small></div><div class="add">+</div></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sCreateObject__item">
                            <p>История</p>
                            <ul class="crm-history">
                                <li x-show="savedAt" x-cloak><b>Замер изменён</b><span x-text="savedLabel() + ' · вы'"></span></li>
                                @foreach (array_reverse($history) as [$when, $what, $who])
                                    <li><b>{{ $what }}</b><span>{{ $when }} · {{ $who }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sCreateObject__footer">
            <div class="sCreateObject__footer-row">
                <button type="button" class="sCreateObject__btn btn btn-accent" @click="save()" :disabled="!dirty()" x-text="dirty() ? 'Сохранить замер' : (savedAt ? 'Сохранено ✓' : 'Сохранить замер')"></button>
                <button type="button" class="sCreateObject__btn btn btn-light" x-show="dirty()" x-cloak @click="cancel()">Отменить изменения</button>
                <button type="button" class="sCreateObject__btn btn btn-light" x-show="savedAt && !dirty()" x-cloak @click="resetProject()">Вернуть по проекту</button>
                <a class="sCreateObject__btn btn btn-light" href="{{ $flatUrl }}" @click="leave($event)">К квартире {{ $flat['n'] }}</a>
                @if ($prev)<a class="sCreateObject__btn btn btn-light" href="{{ route('concept.room', [$house, $flat['n'], $i - 1]) }}" @click="leave($event)">← {{ $prev['name'] }}</a>@endif
                @if ($next)<a class="sCreateObject__btn btn btn-light" href="{{ route('concept.room', [$house, $flat['n'], $i + 1]) }}" @click="leave($event)">{{ $next['name'] }} →</a>@endif
            </div>
        </div>
        <div class="crm-toast" x-cloak x-show="toast" x-transition x-text="toast"></div>
    </div>
    <a class="close-btn" href="{{ $flatUrl }}" title="Закрыть" onclick="return !window.__roomDirty || confirm('Замер изменён и не сохранён. Уйти без сохранения?')"><svg class="icon icon-close"><use xlink:href="{{ $sprite }}#close"></use></svg></a>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('roomMeasure', (d) => ({
                a: String(d.a), b: String(d.b), h: String(d.h), openings: d.openings.map(o => ({ ...o, width: String(o.width), height: String(o.height) })),
                savedAt: null, base: '', toast: '',
                // Сохранение замера: в концепте — в этом браузере (localStorage), в рабочей версии — в базу
                init() {
                    try {
                        const saved = JSON.parse(localStorage.getItem(d.key) || 'null');
                        if (saved) { this.a = saved.a; this.b = saved.b; this.h = saved.h; this.openings = saved.openings; this.savedAt = saved.at; }
                    } catch (e) {}
                    this.base = this.snapshot();
                    this.$watch(() => this.dirty(), (v) => { window.__roomDirty = v; });
                    window.addEventListener('beforeunload', (e) => { if (this.dirty()) { e.preventDefault(); e.returnValue = ''; } });
                },
                snapshot() { return JSON.stringify([this.a, this.b, this.h, this.openings.map(o => [o.type, o.label, o.width, o.height])]); },
                addOpening(type) {
                    const d = { 'ДВ': ['дверь', '0.8', '2.1'], 'ОК': ['окно', '1.5', '1.4'], 'ПР': ['проём без двери', '0.9', '2.1'] }[type];
                    this.openings.push({ type, label: d[0], width: d[1], height: d[2] });
                },
                dirty() { return this.snapshot() !== this.base; },
                say(t) { this.toast = t; clearTimeout(this._t); this._t = setTimeout(() => this.toast = '', 2500); },
                save() {
                    const at = new Date().toISOString();
                    try { localStorage.setItem(d.key, JSON.stringify({ a: this.a, b: this.b, h: this.h, openings: this.openings, at,
                        floor: this.floor(), walls: this.walls(), perimeter: this.perimeter() })); } catch (e) { return this.say('Не удалось сохранить — браузер запретил хранение'); }
                    this.savedAt = at; this.base = this.snapshot(); this.say('Замер сохранён — площади обновлены в квартире');
                },
                cancel() { const b = JSON.parse(this.base); this.a = b[0]; this.b = b[1]; this.h = b[2]; this.openings = b[3].map(([type, label, width, height]) => ({ type, label, width, height })); },
                resetProject() {
                    if (!confirm('Вернуть размеры по проекту? Сохранённый замер будет удалён.')) return;
                    try { localStorage.removeItem(d.key); } catch (e) {}
                    this.a = String(d.a); this.b = String(d.b); this.h = String(d.h);
                    this.openings = d.openings.map(o => ({ ...o, width: String(o.width), height: String(o.height) }));
                    this.savedAt = null; this.base = this.snapshot(); this.say('Размеры возвращены по проекту');
                },
                leave(e) { if (this.dirty() && !confirm('Замер изменён и не сохранён. Уйти без сохранения?')) e.preventDefault(); else window.__roomDirty = false; },
                savedLabel() { return this.savedAt ? new Date(this.savedAt).toLocaleString('ru-RU', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : ''; },
                num(v) { const n = parseFloat(String(v).replace(',', '.')); return isNaN(n) ? 0 : n; },
                fmt(v) { return v.toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
                floor() { return this.num(this.a) * this.num(this.b); },
                perimeter() { return 2 * (this.num(this.a) + this.num(this.b)); },
                openingsArea() { return this.openings.reduce((s, o) => s + this.num(o.width) * this.num(o.height), 0); },
                walls() { return Math.max(0, this.perimeter() * this.num(this.h) - this.openingsArea()); },
                perimeterNet() { return Math.max(0, this.perimeter() - this.openings.filter(o => o.type !== 'ОК').reduce((s, o) => s + this.num(o.width), 0)); },
                // чертёж в масштабе: длинная сторона вписывается в 180 px
                scale() { return 180 / Math.max(this.num(this.a), this.num(this.b), 0.1); },
                planW() { return Math.max(20, this.num(this.a) * this.scale()); },
                planH() { return Math.max(20, this.num(this.b) * this.scale()); },
                planX() { return 30 + (180 - this.planW()) / 2; },
                planY() { return 20 + (170 - this.planH()) / 2; },
                // отрезки проёмов: окна по верхней стене, двери по нижней, в масштабе ширины
                marks(type) {
                    const list = this.openings.filter(o => type === 'ОК' ? o.type === 'ОК' : o.type !== 'ОК'), y = type === 'ОК' ? this.planY() : this.planY() + this.planH();
                    const step = this.planW() / (list.length + 1);
                    return list.map((o, k) => {
                        const w = Math.min(step * 0.8, this.num(o.width) * this.scale()), x = this.planX() + step * (k + 1) - w / 2;
                        return `M${x.toFixed(1)} ${y.toFixed(1)} H${(x + w).toFixed(1)}`;
                    }).join(' ');
                },
            }));
        });
    </script>
@endsection
