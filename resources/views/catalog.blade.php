@extends('layouts.app', ['title' => 'Справочник — CRM Лидерпрофстрой'])

@php($fmt = fn ($v, $d = 2) => $v === null ? 'уточнить' : number_format((float) $v, $d, ',', ' '))

@section('content')
    @include('partials.page-head', ['heading' => 'Справочник норм и цен', 'crumbs' => ['Справочник' => route('catalog')]])
    <div class="section"><div class="container-fluid">
        <p class="text-secondary">Нормы и цены — из сметы по Бетанкура от 20.05.2024, стяжка и шпатлёвка — по файлу формул (цен нет).
            Цены с НДС. Редактирование здесь — следующий этап, пока меняем по вашей просьбе.</p>
        @foreach ($workTypes as $wt)
            <div class="crm-card">
                <h4>{{ $wt['name'] }}</h4>
                <p class="text-secondary small">{{ \App\Calc\Catalog::SURFACES[$wt['surface']] ?? '' }} · объём: {{ \App\Calc\Geometry::BASES[$wt['basis']] ?? $wt['basis'] }}
                    · работа {{ $fmt($wt['price']) }} ₽/{{ $wt['unit'] === 'м2' ? 'м²' : $wt['unit'] }}
                    @if (! empty($wt['params'])) · по умолчанию: {{ collect($wt['params'])->map(fn ($v, $k) => ($k === 'thickness' ? 'слой ' . $v . ' мм' : 'слоёв ' . $v))->implode(', ') }}@endif</p>
                <table class="crm-table">
                    <thead><tr><th>Материал</th><th>Формула</th><th>Норма</th><th>Округление</th><th>Цена</th></tr></thead>
                    @foreach ($wt['materials'] as $m)
                        @php($mat = $materials[$m['material']])
                        <tr><td>{{ $m['label'] ?? $mat['name'] }}</td>
                            <td>{{ \App\Calc\Estimator::FORMULAS[$m['formula']] ?? $m['formula'] }}@isset($m['basis']) ({{ \App\Calc\Geometry::BASES[$m['basis']] }})@endisset</td>
                            <td class="num">{{ rtrim(rtrim(number_format($m['norm'], 3, ',', ' '), '0'), ',') }} {{ $mat['unit'] }}</td>
                            <td class="num">вверх до {{ rtrim(rtrim(number_format($m['step'] ?? 1, 1, ',', ''), '0'), ',') }}</td>
                            <td class="num">{{ $fmt($mat['price']) }} ₽/{{ $mat['unit'] }}</td></tr>
                    @endforeach
                </table>
            </div>
        @endforeach
        @foreach ($extras as $e)
            <div class="crm-card"><h4>{{ $e['name'] }}</h4><p class="text-secondary small">{{ $fmt($e['price']) }} ₽/{{ $e['unit'] }} · {{ $e['rule'] }}</p></div>
        @endforeach
    </div></div>
@endsection
