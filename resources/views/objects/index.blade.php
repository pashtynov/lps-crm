@extends('layouts.app', ['title' => 'Объекты — CRM Лидерпрофстрой', 'bodyClass' => 'main-page'])

@php($plural = fn ($n, $one, $few, $many) => $n % 10 === 1 && $n % 100 !== 11 ? $one : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $few : $many))

@section('content')
    @include('partials.page-head', ['heading' => 'Объекты', 'crumbs' => []])

    {{-- Карточки объектов — разметка макета index.html (sObject) --}}
    <div class="sObject section" id="sObject">
        <div class="container-fluid">
            <div class="sObject__row row">
                @foreach ($objects as $o)
                    @php([$color, $badge, $icon] = $o->statusStyle())
                    <div class="sObject__col col-sm-6 col-lg-4 col-xxl-auto">
                        <div class="sObject-card" style="--bgDefColor: {{ $color }}">
                            <a class="h4" href="{{ route('objects.show', $o) }}">{{ $o->name }}</a>
                            <p>@if ($o->rooms_count || ! $o->lines_count){{ $o->rooms_count }} {{ $plural($o->rooms_count, 'помещение', 'помещения', 'помещений') }}@else{{ $o->lines_count }} {{ $plural($o->lines_count, 'позиция', 'позиции', 'позиций') }} по объёмам@endif
                                · изм. {{ $o->updated_at->format('d.m.Y') }}</p>
                            <div class="sObject-card__row row">
                                <div class="col">
                                    <div class="badge {{ $badge }}"><img src="{{ asset('verstka/img/svg/' . $icon) }}" alt="" loading="lazy"/>{{ $o->statusLabel() }}</div>
                                </div>
                                <div class="col-auto">
                                    <a class="sObject-card__link" href="{{ route('estimate', $o) }}" title="Смета">
                                        <svg class="icon icon-edit"><use xlink:href="{{ asset('verstka/img/svg/sprite.svg') }}#edit"></use></svg>
                                    </a>
                                </div>
                            </div>
                            <div class="sObject-card__line" style="--width: {{ in_array($o->status, ['done', 'archive']) ? '100%' : '0%' }}"></div>
                        </div>
                    </div>
                @endforeach

                {{-- Концепт следующих этапов: примерный ЖК, в базе не хранится --}}
                @php($conceptPercent = \App\Support\ConceptData::overall())
                <div class="sObject__col col-sm-6 col-lg-4 col-xxl-auto">
                    <div class="sObject-card crm-card-concept" style="--bgDefColor: #467CF4">
                        <a class="h4" href="{{ route('concept') }}">{{ \App\Support\ConceptData::OBJECT }}</a>
                        <p>{{ count(\App\Support\ConceptData::HOUSES) }} дома · {{ count(\App\Support\ConceptData::units(null, null, false)) }} квартир · готовность {{ $conceptPercent }}%</p>
                        <div class="sObject-card__row row">
                            <div class="col">
                                <div class="badge badge-primary"><img src="{{ asset('verstka/img/svg/play.svg') }}" alt="" loading="lazy"/>В работе</div>
                            </div>
                            <div class="col-auto"><span class="crm-concept-badge">концепт</span></div>
                        </div>
                        <div class="sObject-card__line" style="--width: {{ $conceptPercent }}%"></div>
                    </div>
                </div>

                <div class="sObject__col col-sm-6 col-lg-4 col-xxl-auto">
                    <div class="sObject-card sObject-card--addNew">
                        <div class="sObject-card__icon">
                            <svg class="icon icon-plus"><use xlink:href="{{ asset('verstka/img/svg/sprite.svg') }}#plus"></use></svg>
                        </div>
                        <a class="sObject-card__add" href="#new-object" onclick="document.getElementById('new-object').hidden=false;document.querySelector('#new-object input').focus();return false;">Добавить объект</a>
                    </div>
                </div>
            </div>

            <form class="crm-card mt-4" id="new-object" method="post" action="{{ route('objects.store') }}" @unless ($errors->any()) hidden @endunless>
                @csrf
                <h3>Новый объект</h3>
                <div class="row g-3">
                    <div class="col-md-4"><input class="form-control" name="name" value="{{ old('name') }}" placeholder="Название, например ЖК Видный" required></div>
                    <div class="col-md-4"><input class="form-control" name="address" value="{{ old('address') }}" placeholder="Адрес"></div>
                    <div class="col-md-3"><input class="form-control" name="customer" value="{{ old('customer') }}" placeholder="Заказчик"></div>
                    <div class="col-md-1"><button class="btn btn-accent w-100">Создать</button></div>
                </div>
            </form>
        </div>
    </div>
@endsection
