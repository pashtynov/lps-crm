@extends('layouts.app', ['title' => 'Бригады — концепт', 'bodyClass' => 'object-page'])

@section('content')
    @include('concept._head')
    @php($fmt = fn ($v) => number_format((float) $v, 0, ',', ' '))
    <div class="container-fluid">
        <div class="crm-brigades">
            @foreach ($brigades as $b)
                <div class="crm-brigade" style="--c: {{ $b['color'] }}">
                    <div class="crm-brigade__head">
                        <div><h3>{{ $b['name'] }}</h3><span class="crm-muted">{{ $b['lead'] }} · {{ $b['people'] }} чел.</span></div>
                        <span class="crm-tag">{{ $b['work_name'] }}</span>
                    </div>
                    <div class="crm-brigade__where {{ $b['flats_work'] ? '' : 'idle' }}">
                        <span>{{ $b['flats_work'] ? 'Сейчас' : 'Простой' }}</span><b>{{ $b['where'] }}</b>
                    </div>
                    <div class="crm-brigade__nums">
                        <div><span>Сделано</span><b>{{ $fmt($b['done']) }} м²</b></div>
                        <div><span>Квартир в работе</span><b>{{ $b['flats_work'] }}</b></div>
                        <div><span>Начислено с начала объекта</span><b>{{ $fmt($b['earned']) }} ₽</b></div>
                    </div>
                </div>
            @endforeach
            <div class="crm-brigade crm-brigade--alert">
                <h3>Нужна бригада</h3>
                <p>Шпатлёвка, дом 2, подъезд 1, этаж 3 — фронт работ готов, бригада не назначена.</p>
                <a class="btn btn-light btn-sm" href="{{ route('concept.section', [2, 1]) }}">Назначить на шахматке</a>
            </div>
        </div>
        <p class="crm-muted mt-3">Начислено = принятые и сделанные м² × сдельная ставка вида работ — как в ваших таблицах монтажа потолков (багет и натяжка по 50 ₽/м² делятся на бригаду).
            Состав бригады, документы и телефоны рабочих — в карточке бригады.</p>
    </div>
@endsection
