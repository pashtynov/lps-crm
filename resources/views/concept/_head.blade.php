{{-- Шапка концепта: что это, сводка директора, вкладки --}}
@php
    $fmt = fn ($v, $d = 0) => number_format((float) $v, $d, ',', ' ');
    $sprite = asset('verstka/img/svg/sprite.svg');
    $moneyDone = collect($progress)->sum('money_done');
    $moneyTotal = collect($progress)->sum('money_total');
    $people = collect($brigades)->filter(fn ($b) => $b['flats_work'] > 0)->sum('people');
    $maxLag = collect($schedule['rows'])->max('lag_days');
    $icons = ['chess' => 'tile', 'schedule' => 'gantt', 'brigades' => 'workers', 'today' => 'check'];
@endphp
<div class="page-head">
    <div class="container-fluid">
        <div class="page-head__row row align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb"><ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('objects.index') }}"><span>Объекты</span></a></li>
                    <li class="breadcrumb-item active"><a href="#"><span>{{ \App\Support\ConceptData::OBJECT }}</span></a></li>
                </ol></nav>
            </div>
            <div class="page-head__col page-head__col--title col-lg order-md-4">
                <h1>{{ \App\Support\ConceptData::OBJECT }} <span class="crm-status" style="--c:#467CF4"><img src="{{ asset('verstka/img/svg/play.svg') }}" alt="">В работе</span></h1>
            </div>
            <div class="col-12 d-none d-md-block m-0 p-0 order-md-3"></div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="crm-concept-note">
        <b>Концепт следующих этапов.</b> Данные примерные, изменения на этих экранах не сохраняются.
        Так будут выглядеть учёт работ, бригады и график — из тех же замеров, что и смета.
    </div>

    <div class="crm-kpis">
        <div class="crm-kpi"><span>Выполнено на</span><b>{{ $fmt($moneyDone / 1e6, 1) }} млн ₽</b><small>из {{ $fmt($moneyTotal / 1e6, 1) }} млн ₽ по смете</small></div>
        <div class="crm-kpi"><span>Людей на объекте сегодня</span><b>{{ $people }}</b><small>{{ collect($brigades)->filter(fn ($b) => $b['flats_work'] > 0)->count() }} бригады в работе</small></div>
        <div class="crm-kpi {{ $maxLag > 3 ? 'crm-kpi--bad' : '' }}"><span>Максимальное отставание</span><b>{{ $maxLag > 0 ? $maxLag . ' дн' : 'нет' }}</b><small>{{ collect($schedule['rows'])->sortByDesc('lag_days')->first()['name'] }}, подъезд {{ collect($schedule['rows'])->sortByDesc('lag_days')->first()['section'] }}</small></div>
        <div class="crm-kpi crm-kpi--works">
            @foreach ($progress as $p)
                <div class="work-status crm-work-status">
                    <div class="row"><div class="col">{{ $p['name'] }}</div><div class="col-auto fw-500">{{ $p['percent'] }}%</div></div>
                    <div class="line" style="--w: {{ $p['percent'] }}%; --bgColor: {{ $p['color'] }}"></div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="crm-tabs-row crm-concept-tabs">
        <a class="toggle-block__btn btn btn-outline-light" href="{{ route('objects.index') }}" title="Вернуться к рабочим объектам">
            <svg class="icon icon-chevron-left"><use xlink:href="{{ $sprite }}#chevron-left"></use></svg><span>Объекты</span></a>
        @foreach (\App\Http\Controllers\ConceptController::TABS as $k => $label)
            <a class="toggle-block__btn btn btn-outline-light {{ $tab === $k ? 'active' : '' }}" href="{{ route('concept', $k) }}">
                <svg class="icon icon-{{ $icons[$k] }}"><use xlink:href="{{ $sprite }}#{{ $icons[$k] }}"></use></svg><span>{{ $label }}</span></a>
        @endforeach
    </div>
</div>
