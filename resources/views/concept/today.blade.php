@extends('layouts.app', ['title' => 'Сегодня — концепт', 'bodyClass' => 'object-page'])

@section('content')
    @include('concept._head')
    @php
        // задания на сегодня: работы «в работе», сгруппированные по бригаде, подъезду и этажу
        $tasks = [];
        $C = \App\Support\ConceptData::class;
        foreach ($C::units(null, null, false) as $f) {
            foreach ($f['works'] as $code => $w) {
                if ($w['status'] !== 'work') continue;
                $k = ($w['brigade'] ?? 0) . "-$code-{$f['house']}-{$f['section']}-{$f['floor']}";
                $tasks[$k] ??= ['brigade' => $w['brigade'] ? $C::BRIGADES[$w['brigade']]['name'] : 'Без бригады',
                    'work' => $C::WORKS[$code]['name'], 'color' => $C::WORKS[$code]['color'],
                    'where' => "Дом {$f['house']}, подъезд {$f['section']}, этаж {$f['floor']}", 'flats' => [], 'm2' => 0, 'house' => $f['house']];
                $tasks[$k]['flats'][] = $f['n'];
                $tasks[$k]['m2'] += $f[$C::WORKS[$code]['basis']];
            }
        }
        $tasks = array_values($tasks);
    @endphp
    <div class="container-fluid">
        <div class="crm-today" x-data="{ done: {}, closed: false }">
            <div class="crm-phone">
                <div class="crm-phone__bar"><span>9:41</span><span>Прораб · {{ \App\Support\ConceptData::OBJECT }}</span></div>
                <div class="crm-phone__screen">
                    <h3>Сегодня, {{ now()->translatedFormat('j F') }}</h3>
                    <p class="crm-muted">{{ count($tasks) }} заданий · отметьте, что сделано</p>
                    @foreach ($tasks as $i => $t)
                        <div class="crm-task" :class="done[{{ $i }}] && 'is-done'" style="--c: {{ $t['color'] }}">
                            <div class="crm-task__top"><b>{{ $t['brigade'] }}</b><span class="crm-tag">{{ $t['work'] }}</span></div>
                            <div class="crm-task__where">{{ $t['where'] }} · кв. {{ implode(', ', $t['flats']) }}</div>
                            <div class="crm-muted">{{ number_format($t['m2'], 0, ',', ' ') }} м²</div>
                            @include('concept._problem', ['house' => $t['house'], 'ns' => json_encode($t['flats']), 'addLabel' => 'Проблема'])
                            <div class="crm-task__btns">
                                <button type="button" class="btn btn-accent" @click="done[{{ $i }}] = !done[{{ $i }}]" x-text="done[{{ $i }}] ? '✓ Сделано' : 'Сделано'"></button>
                                <button type="button" class="btn btn-light" title="Фото с камеры">📷</button>
                            </div>
                        </div>
                    @endforeach
                    <button type="button" class="btn btn-accent w-100 mt-2" @click="closed = true" x-show="!closed">Закрыть день</button>
                    <div class="crm-flash" x-show="closed" x-cloak x-text="`День закрыт: отмечено ${Object.values(done).filter(Boolean).length} заданий. Запись в журнал работ и процент готовности обновлены.`"></div>
                </div>
            </div>
            <div class="crm-today__about">
                <h3>Экран прораба</h3>
                <p>Прораб утром видит, какая бригада где работает, вечером отмечает «Сделано» — одна кнопка на задание.</p>
                <ul class="crm-list">
                    <li>Объём в м² берётся из замеров — вручную ничего не пересчитывают.</li>
                    <li>Отметки сразу двигают процент готовности на шахматке и факт в графике.</li>
                    <li>«Закрыть день» — готовая запись в электронный журнал работ.</li>
                    <li>«Проблема» с фото — руководитель видит её на шахматке красным.</li>
                    <li>Принятые м² — основание для сдельной оплаты бригады и для актов КС-2.</li>
                </ul>
                <p class="crm-muted">Работает на телефоне, кнопки под палец. Без интернета отметки копятся и отправляются, когда связь появится.</p>
            </div>
        </div>
    </div>
@endsection
