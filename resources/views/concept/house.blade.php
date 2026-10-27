@extends('layouts.app', ['title' => 'Концепт — ' . $h['name'], 'bodyClass' => 'object-page'])

@section('content')
    @php
        $C = \App\Support\ConceptData::class;
        $n = fn ($v, $d = 0) => number_format((float) $v, $d, ',', ' ');
    @endphp
    @include('concept._page', ['crumbs' => [$C::OBJECT => route('concept'), $h['name'] => route('concept.house', $house)],
        'title' => $h['name'], 'status' => $h['status'], 'statusColor' => $house === 1 ? '#1DBA6F' : '#467CF4',
        'subtitle' => $C::OBJECT . ' · ' . count($h['sections']) . ' подъезда · ' . $h['floors'] . ' этажей'])
    <div class="container-fluid">
        @include('concept._scope', ['house' => $house, 'extraCounts' => ['Подъездов' => count($h['sections'])]])

        <div class="main-table crm-concept-table">
            <table>
                <thead><tr><th>Подъезд</th><th>Квартир на этаже</th><th>Квартир</th><th>МОП</th><th>Стены, м²</th><th>Готовность</th><th>По видам работ</th></tr></thead>
                <tbody>
                @foreach ($h['sections'] as $s => $types)
                    @php($c = $C::counts($house, $s))
                    <tr class="crm-row-link" onclick="location.href='{{ route('concept.section', [$house, $s]) }}'">
                        <td><div class="main-table__title"><a class="h3" href="{{ route('concept.section', [$house, $s]) }}">Подъезд {{ $s }}</a></div>
                            <span class="crm-muted">{{ collect($types)->countBy()->map(fn ($k, $t) => "$t × $k")->implode(', ') }}</span></td>
                        <td>{{ count($types) }}</td><td>{{ $c['flats'] }}</td><td>{{ $c['mop'] }}</td><td>{{ $n($c['walls']) }}</td>
                        <td><div class="main-table__progress"><img src="{{ asset('verstka/img/svg/finish.svg') }}" alt="">{{ $C::overall($house, $s) }}%</div></td>
                        <td class="crm-mini-bars">
                            @foreach ($C::progress($house, $s) as $w)
                                <div title="{{ $w['name'] }}: {{ $w['percent'] }}%"><i style="--w: {{ $w['percent'] }}%; --c: {{ $w['color'] }}"></i><span>{{ $w['name'] }}</span></div>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
