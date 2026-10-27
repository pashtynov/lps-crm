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

    <div class="sCreateObject" x-data="roomMeasure(@js(['a' => $room['a'], 'b' => $room['b'], 'h' => $C::HEIGHT, 'openings' => $room['openings']]))">
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
                        <p class="crm-hint">Поменяйте размер — площади пересчитаются. В концепте изменения не сохраняются.</p>
                    </div>
                </div>

                <h4 class="crm-h4">Замер</h4>
                <div class="row g-3">
                    <div class="col-4"><label class="crm-label">Длина A, м</label><input class="form-control" inputmode="decimal" x-model="a"></div>
                    <div class="col-4"><label class="crm-label">Ширина B, м</label><input class="form-control" inputmode="decimal" x-model="b"></div>
                    <div class="col-4"><label class="crm-label">Высота H, м</label><input class="form-control" inputmode="decimal" x-model="h"></div>
                </div>

                <h4 class="crm-h4">Проёмы</h4>
                <table class="crm-table">
                    <thead><tr><th>Тип</th><th>Что</th><th>Ширина, м</th><th>Высота, м</th><th>Площадь, м²</th></tr></thead>
                    <tbody>
                    <template x-for="(o, k) in openings" :key="k">
                        <tr>
                            <td><span class="crm-tag" x-text="o.type"></span></td>
                            <td x-text="o.label"></td>
                            <td><input class="form-control form-control-sm" inputmode="decimal" x-model="o.width"></td>
                            <td><input class="form-control form-control-sm" inputmode="decimal" x-model="o.height"></td>
                            <td x-text="fmt(num(o.width) * num(o.height))"></td>
                        </tr>
                    </template>
                    </tbody>
                </table>

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
                    <div class="col-12"><div class="sCreateObject__item"><p>Замер</p><span>{{ $flat['measured'] ? '14.10.2026, замерщик Алексей К.' : 'не сделан — нет доступа' }}</span></div></div>
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
                <a class="sCreateObject__btn btn btn-accent" href="{{ $flatUrl }}">К квартире {{ $flat['n'] }}</a>
                @if ($prev)<a class="sCreateObject__btn btn btn-light" href="{{ route('concept.room', [$house, $flat['n'], $i - 1]) }}">← {{ $prev['name'] }}</a>@endif
                @if ($next)<a class="sCreateObject__btn btn btn-light" href="{{ route('concept.room', [$house, $flat['n'], $i + 1]) }}">{{ $next['name'] }} →</a>@endif
            </div>
        </div>
    </div>
    <a class="close-btn" href="{{ $flatUrl }}" title="Закрыть"><svg class="icon icon-close"><use xlink:href="{{ $sprite }}#close"></use></svg></a>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('roomMeasure', (d) => ({
                a: String(d.a), b: String(d.b), h: String(d.h), openings: d.openings.map(o => ({ ...o, width: String(o.width), height: String(o.height) })),
                num(v) { const n = parseFloat(String(v).replace(',', '.')); return isNaN(n) ? 0 : n; },
                fmt(v) { return v.toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
                floor() { return this.num(this.a) * this.num(this.b); },
                perimeter() { return 2 * (this.num(this.a) + this.num(this.b)); },
                openingsArea() { return this.openings.reduce((s, o) => s + this.num(o.width) * this.num(o.height), 0); },
                walls() { return Math.max(0, this.perimeter() * this.num(this.h) - this.openingsArea()); },
                perimeterNet() { return Math.max(0, this.perimeter() - this.openings.filter(o => o.type === 'ДВ').reduce((s, o) => s + this.num(o.width), 0)); },
                // чертёж в масштабе: длинная сторона вписывается в 180 px
                scale() { return 180 / Math.max(this.num(this.a), this.num(this.b), 0.1); },
                planW() { return Math.max(20, this.num(this.a) * this.scale()); },
                planH() { return Math.max(20, this.num(this.b) * this.scale()); },
                planX() { return 30 + (180 - this.planW()) / 2; },
                planY() { return 20 + (170 - this.planH()) / 2; },
                // отрезки проёмов: окна по верхней стене, двери по нижней, в масштабе ширины
                marks(type) {
                    const list = this.openings.filter(o => o.type === type), y = type === 'ОК' ? this.planY() : this.planY() + this.planH();
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
