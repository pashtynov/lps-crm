@extends('layouts.app', ['title' => 'Концепт — ' . \App\Support\ConceptData::OBJECT, 'bodyClass' => 'object-page'])

@section('content')
    @include('concept._head')
    @php
        $C = \App\Support\ConceptData::class;
        $n = fn ($v, $d = 0) => number_format((float) $v, $d, ',', ' ');
        $sprite = asset('verstka/img/svg/sprite.svg');
    @endphp
    <div class="container-fluid">
        <div class="main-table crm-concept-table">
            <table>
                <thead><tr><th>Дом</th><th>Подъездов</th><th>Этажей</th><th>Квартир</th><th>МОП</th><th>Стены, м²</th><th>Готовность</th><th>По видам работ</th></tr></thead>
                <tbody>
                @foreach ($C::HOUSES as $id => $h)
                    @php($c = $C::counts($id))
                    @php($o = $C::overall($id))
                    <tr class="crm-row-link" onclick="location.href='{{ route('concept.house', $id) }}'">
                        <td><div class="main-table__title"><a class="h3" href="{{ route('concept.house', $id) }}">{{ $h['name'] }}</a></div><span class="crm-muted">{{ $h['status'] }}</span></td>
                        <td>{{ count($h['sections']) }}</td><td>{{ $h['floors'] }}</td><td>{{ $c['flats'] }}</td><td>{{ $c['mop'] }}</td><td>{{ $n($c['walls']) }}</td>
                        <td><div class="main-table__progress"><img src="{{ asset('verstka/img/svg/finish.svg') }}" alt="">{{ $o }}%</div></td>
                        <td class="crm-mini-bars">
                            @foreach ($C::progress($id) as $w)
                                <div title="{{ $w['name'] }}: {{ $w['percent'] }}%"><i style="--w: {{ $w['percent'] }}%; --c: {{ $w['color'] }}"></i><span>{{ $w['name'] }}</span></div>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex align-items-center gap-3 mt-3 flex-wrap">
            <a class="btn btn-accent" href="{{ route('concept.new') }}">+ Новый объект — мастер</a>
            <span class="crm-muted">Иерархия как в макете: объект → дом → подъезд (квартиры и МОП) → квартира → помещения. Нажмите на дом.
                Новый объект собирается из типовых планировок и схем подъездов.</span>
        </div>
    </div>
@endsection
