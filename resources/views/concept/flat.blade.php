@extends('layouts.app', ['title' => "Концепт — квартира {$flat['n']}", 'bodyClass' => 'object-page'])

@section('content')
    @php
        $C = \App\Support\ConceptData::class;
        $n = fn ($v, $d = 1) => number_format((float) $v, $d, ',', ' ');
        $statusNames = ['todo' => 'не начато', 'work' => 'в работе', 'done' => 'сделано', 'accepted' => 'принято'];
        $rooms = $flat['rooms'];
        // в квартире «в работе» часть помещений уже сделана — так прораб и отмечает, по помещениям
        $roomStatus = function (int $i, string $flatStatus) use ($rooms) {
            return $flatStatus === 'work' ? ($i < ceil(count($rooms) / 2) ? 'done' : 'work') : $flatStatus;
        };
        $prev = $C::flat($house, $flat['n'] - 1); $next = $C::flat($house, $flat['n'] + 1);
        $doneMoney = 0; $allMoney = 0;
        foreach ($flat['works'] as $code => $w) {
            $m2 = $flat[$C::WORKS[$code]['basis']]; $allMoney += $m2 * $C::WORKS[$code]['price'];
            $doneMoney += in_array($w['status'], ['done', 'accepted'], true) ? $m2 * $C::WORKS[$code]['price'] : 0;
        }
    @endphp
    @include('concept._page', ['crumbs' => [$C::OBJECT => route('concept'), $h['name'] => route('concept.house', $house),
            "Подъезд {$flat['section']}" => route('concept.section', [$house, $flat['section']]), "Квартира {$flat['n']}" => '#'],
        'title' => "Квартира {$flat['n']}", 'subtitle' => "{$h['name']} · подъезд {$flat['section']} · этаж {$flat['floor']} · планировка {$flat['type']}"])

    <div class="container-fluid">
        <div class="crm-flat-nav">
            @if ($prev)<a class="btn btn-light btn-sm" href="{{ route('concept.flat', [$house, $prev['n']]) }}">← кв. {{ $prev['n'] }}</a>@endif
            <a class="btn btn-light btn-sm" href="{{ route('concept.section', [$house, $flat['section']]) }}">К шахматке подъезда</a>
            @if ($next)<a class="btn btn-light btn-sm" href="{{ route('concept.flat', [$house, $next['n']]) }}">кв. {{ $next['n'] }} →</a>@endif
        </div>

        @if ($flat['problem'])<div class="crm-warning">⚠ {{ $flat['problem'] }} — отмечено прорабом, видно красным на шахматке.</div>@endif

        <div class="crm-kpis crm-kpis--flat">
            <div class="crm-kpi"><span>Помещений</span><b>{{ count($rooms) }}</b><small>замер {{ $flat['measured'] ? 'есть' : 'не сделан — нет доступа' }}</small></div>
            <div class="crm-kpi"><span>Стены / пол</span><b>{{ $n($flat['walls']) }} / {{ $n($flat['floor_m2']) }}</b><small>м², из замера помещений</small></div>
            <div class="crm-kpi"><span>Готовность</span><b>{{ $allMoney ? round($doneMoney / $allMoney * 100) : 0 }}%</b><small>по стоимости работ в квартире</small></div>
            <div class="crm-kpi crm-kpi--works">
                @foreach ($flat['works'] as $code => $w)
                    <div class="crm-flat-work">
                        <span class="crm-dot" style="--c: {{ $C::WORKS[$code]['color'] }}"></span>
                        <span>{{ $C::WORKS[$code]['name'] }}</span>
                        <span class="crm-st st-{{ $w['status'] }}">{{ $statusNames[$w['status']] }}</span>
                        <small>{{ $w['brigade'] ? $C::BRIGADES[$w['brigade']]['name'] : ($w['status'] === 'todo' ? '' : 'без бригады') }}</small>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="main-table crm-concept-table crm-mop">
            <table>
                <thead><tr><th>Помещение</th><th>Размеры, м</th><th>Пол, м²</th><th>Стены, м²</th>@foreach ($C::WORKS as $w)<th>{{ $w['name'] }}</th>@endforeach</tr></thead>
                <tbody>
                @foreach ($rooms as $i => $r)
                    <tr class="crm-row-link" onclick="location.href='{{ route('concept.room', [$house, $flat['n'], $i]) }}'">
                        <td><a class="crm-room-link" href="{{ route('concept.room', [$house, $flat['n'], $i]) }}"><b>{{ $r['name'] }}</b><span>замер →</span></a></td>
                        <td>{{ $n($r['a']) }} × {{ $n($r['b']) }}, h {{ $n($C::HEIGHT) }}</td>
                        <td>{{ $n($r['floor_m2'], 2) }}</td><td>{{ $n($r['walls'], 2) }}</td>
                        @foreach ($flat['works'] as $code => $w)
                            @php($st = $roomStatus($i, $w['status']))
                            <td><span class="crm-st st-{{ $st }}">{{ $statusNames[$st] }}</span></td>
                        @endforeach
                    </tr>
                @endforeach
                <tr class="crm-subtotal"><td>Итого</td><td></td><td>{{ $n($flat['floor_m2'], 2) }}</td><td>{{ $n($flat['walls'], 2) }}</td><td colspan="{{ count($C::WORKS) }}"></td></tr>
                </tbody>
            </table>
        </div>
        <p class="crm-muted mt-2">Нажмите на помещение — откроется его замер. Статусы по помещениям прораб отмечает с телефона.
            Площади отсюда идут и в смету, и в объём работ бригады.</p>
    </div>
@endsection
