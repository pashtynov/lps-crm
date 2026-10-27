@extends('layouts.app', ['title' => 'График работ — концепт', 'bodyClass' => 'object-page'])

@section('content')
    @include('concept._head')
    @php
        $start = \Carbon\Carbon::parse($schedule['start']);
        $weeks = $schedule['weeks'];
        $pct = fn ($w) => round(($w - 1) / $weeks * 100, 3);
    @endphp
    <div class="container-fluid">
        <div class="crm-panel crm-gantt-panel">
            <div class="crm-gantt-legend">
                <span><i class="plan"></i>план</span><span><i class="fact"></i>факт — сделано</span><span><i class="today"></i>сегодня</span>
                <span class="crm-muted">План собирается из объёмов замера и выработки бригады (м²/день), факт — из отметок прораба</span>
            </div>
            <div class="crm-gantt">
                <div class="crm-gantt__head">
                    <div class="crm-gantt__label"></div>
                    <div class="crm-gantt__track">
                        @for ($w = 1; $w <= $weeks; $w++)
                            <div class="crm-gantt__week" style="left: {{ $pct($w) }}%; width: {{ round(100 / $weeks, 3) }}%">
                                {{ $start->copy()->addWeeks($w - 1)->translatedFormat('j M') }}
                            </div>
                        @endfor
                    </div>
                </div>
                @foreach ($schedule['rows'] as $r)
                    @php
                        $left = $pct($r['from']); $width = round(($r['to'] - $r['from'] + 1) / $weeks * 100, 3);
                    @endphp
                    <div class="crm-gantt__row">
                        <div class="crm-gantt__label">
                            <b>{{ $r['name'] }}</b><small>подъезд {{ $r['section'] }}</small>
                            @if ($r['lag_days'] > 2)
                                <span class="crm-lag bad">−{{ $r['lag_days'] }} дн</span>
                            @elseif ($r['lag_days'] < 0)
                                <span class="crm-lag good">+{{ abs($r['lag_days']) }} дн</span>
                            @elseif ($r['plan_share'] > 0)
                                <span class="crm-lag">в графике</span>
                            @endif
                        </div>
                        <div class="crm-gantt__track">
                            <div class="crm-gantt__plan" style="left: {{ $left }}%; width: {{ $width }}%; --c: {{ $r['color'] }}">
                                <div class="crm-gantt__fact" style="width: {{ round($r['fact'] * 100) }}%"></div>
                                <span>{{ round($r['fact'] * 100) }}%</span>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="crm-gantt__today" style="left: calc(220px + (100% - 220px) * {{ round(($schedule['today'] - 1) / $weeks, 4) }})"></div>
            </div>
            <p class="crm-muted mt-3">Отставание считается так: сколько должно быть сделано по плану на сегодня и сколько сделано по факту, в пересчёте на дни.
                Клик по строке в рабочей версии откроет шахматку с этим видом работ.</p>
        </div>
    </div>
@endsection
