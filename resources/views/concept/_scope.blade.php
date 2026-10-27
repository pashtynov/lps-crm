{{-- Сводка уровня (объект / дом / подъезд): готовность в ₽, счётчики, прогресс по видам работ в м² --}}
@php
    $C = \App\Support\ConceptData::class;
    $p = $C::progress($house ?? null, $section ?? null);
    $c = $C::counts($house ?? null, $section ?? null);
    $overall = $C::overall($house ?? null, $section ?? null);
    $n = fn ($v, $d = 0) => number_format((float) $v, $d, ',', ' ');
@endphp
<div class="crm-scope">
    <div class="crm-scope__ring">
        <div class="circular-progress-bar crm-ring" style="--percent: {{ $overall }}; --bgColor: #467CF4;">
            <div class="percent">
                <svg viewBox="0 0 216 216" xmlns="http://www.w3.org/2000/svg"><circle cx="108" cy="108" r="84"></circle><circle cx="108" cy="108" r="84"></circle></svg>
                <div class="number"><h2>{{ $overall }}%</h2><p>готовность</p></div>
            </div>
        </div>
        <small>по стоимости работ</small>
    </div>
    <div class="crm-scope__counts">
        @isset($extraCounts)
            @foreach ($extraCounts as $label => $value)<div><span>{{ $label }}</span><b>{{ $value }}</b></div>@endforeach
        @endisset
        <div><span>Квартир</span><b>{{ $c['flats'] }}</b></div>
        <div><span>МОП</span><b>{{ $c['mop'] }}</b></div>
        <div><span>Помещений в квартирах</span><b>{{ $n($c['rooms']) }}</b></div>
        <div><span>Стены / пол</span><b>{{ $n($c['walls']) }} / {{ $n($c['floor_m2']) }} м²</b></div>
        <div class="{{ $c['problems'] ? 'bad' : '' }}"><span>Проблемы</span><b>{{ $c['problems'] ?: 'нет' }}</b></div>
    </div>
    <div class="crm-scope__works">
        @foreach ($p as $w)
            <div class="work-status crm-work-status" title="Сделано {{ $n($w['done']) }} из {{ $n($w['total']) }} м², в работе {{ $n($w['work']) }} м²">
                <div class="row"><div class="col">{{ $w['name'] }}</div><div class="col-auto fw-500">{{ $w['percent'] }}%</div></div>
                <div class="line" style="--w: {{ $w['percent'] }}%; --bgColor: {{ $w['color'] }}"></div>
            </div>
        @endforeach
    </div>
</div>
