{{-- Уровень объекта: шапка, сводка директора, вкладки --}}
@php
    $C = \App\Support\ConceptData::class;
    $fmt = fn ($v, $d = 0) => number_format((float) $v, $d, ',', ' ');
    $sprite = asset('verstka/img/svg/sprite.svg');
    $moneyDone = collect($progress)->sum('money_done');
    $moneyTotal = collect($progress)->sum('money_total');
    $working = collect($brigades)->filter(fn ($b) => $b['flats_work'] > 0);
    $worst = collect($schedule['rows'])->sortByDesc('lag_days')->first();
    $icons = ['houses' => 'objects', 'schedule' => 'gantt', 'brigades' => 'workers', 'today' => 'check'];
@endphp
@include('concept._page', ['crumbs' => [$C::OBJECT => route('concept')], 'title' => $C::OBJECT, 'status' => 'В работе',
    'subtitle' => 'Нижний Новгород · заказчик — застройщик · ' . count($C::HOUSES) . ' дома'])

<div class="container-fluid">
    <div class="crm-kpis">
        <div class="crm-kpi"><span>Выполнено на</span><b>{{ $fmt($moneyDone / 1e6, 1) }} млн ₽</b><small>из {{ $fmt($moneyTotal / 1e6, 1) }} млн ₽ по смете</small></div>
        <div class="crm-kpi"><span>Людей на объекте сегодня</span><b>{{ $working->sum('people') }}</b><small>{{ $working->count() }} бригад в работе</small></div>
        <div class="crm-kpi {{ $worst['lag_days'] > 3 ? 'crm-kpi--bad' : '' }}"><span>Максимальное отставание</span><b>{{ $worst['lag_days'] > 0 ? $worst['lag_days'] . ' дн' : 'нет' }}</b>
            <small>{{ $worst['name'] }}, дом {{ $worst['house'] }}, подъезд {{ $worst['section'] }}</small></div>
        <div class="crm-kpi crm-kpi--works">
            @foreach ($progress as $p)
                <div class="work-status crm-work-status">
                    <div class="row"><div class="col">{{ $p['name'] }}</div><div class="col-auto fw-500">{{ $p['percent'] }}%</div></div>
                    <div class="line" style="--w: {{ $p['percent'] }}%; --bgColor: {{ $p['color'] }}"></div>
                </div>
            @endforeach
        </div>
    </div>
    @if ($tab === 'houses')@include('concept._problems_list')@endif

    <div class="crm-tabs-row crm-concept-tabs">
        @foreach (\App\Http\Controllers\ConceptController::TABS as $k => $label)
            <a class="toggle-block__btn btn btn-outline-light {{ $tab === $k ? 'active' : '' }}" href="{{ route('concept', $k) }}">
                <svg class="icon icon-{{ $icons[$k] }}"><use xlink:href="{{ $sprite }}#{{ $icons[$k] }}"></use></svg><span>{{ $label }}</span></a>
        @endforeach
    </div>
</div>
