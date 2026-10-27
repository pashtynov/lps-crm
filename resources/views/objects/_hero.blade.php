{{-- Шапка объекта — разметка макета 01-object-d (sPageInfo). $object, $sum = App\Support\ObjectSummary::for() --}}
@php
    $fmt = fn ($v, $d = 0) => number_format((float) $v, $d, ',', ' ');
    [$color, , $icon] = $object->statusStyle();
    $sprite = asset('verstka/img/svg/sprite.svg');
    $e = $sum['estimate'];
@endphp
<div class="page-head">
    <div class="container-fluid">
        <div class="page-head__row row align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('objects.index') }}"><span>Объекты</span></a></li>
                        <li class="breadcrumb-item active"><a href="{{ route('objects.show', $object) }}"><span>{{ $object->name }}</span></a></li>
                    </ol>
                </nav>
            </div>
            <div class="page-head__col col-md-auto order-md-2">
                <a class="page-head__edit" href="{{ route('objects.edit', $object) }}">Редактировать
                    <svg class="icon icon-edit"><use xlink:href="{{ $sprite }}#edit"></use></svg>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="sPageInfo section" id="sPageInfo">
    <div class="sPageInfo__continer container-fluid">
        <div class="sPageInfo__wrap bg-wrap">
            <div class="tabs__wrap">
                <div class="tabs__content active"><img class="picture-bg" src="{{ asset('verstka/img/service-1.jpg') }}" alt="" loading="lazy"/></div>
            </div>
            <div class="sPageInfo__row row">
                <div class="sPageInfo__section-title-col col-md">
                    <div class="section-title">
                        <h1>{{ $object->name }}</h1>
                        <span class="crm-status" style="--c: {{ $color }}"><img src="{{ asset('verstka/img/svg/' . $icon) }}" alt="">{{ $object->statusLabel() }}</span>
                    </div>
                </div>
                <div class="col-md-auto">
                    <div class="sPageInfo__inner-wrap">
                        <div class="task-stat" title="Помещений с видами работ / всего помещений">
                            <svg class="icon icon-fist"><use xlink:href="{{ $sprite }}#fist"></use></svg>{{ $sum['with_works'] }}/{{ $sum['rooms'] }}
                        </div>
                        <div class="startDate" title="Последнее изменение">
                            <svg class="icon icon-calendar"><use xlink:href="{{ $sprite }}#calendar"></use></svg>{{ $object->updated_at->translatedFormat('j M Y') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="sPageInfo__body-row row">
                <div class="col-md">
                    <div class="circular-progress-bar" title="{{ $sum['measured'] }} из {{ $sum['rooms'] }} помещений с размерами. Это не готовность работ — её учёт появится на следующем этапе." style="--percent: {{ $sum['measured_percent'] }}; --bgColor: #467CF4;">
                        <div class="percent">
                            <svg viewBox="0 0 216 216" xmlns="http://www.w3.org/2000/svg"><circle cx="108" cy="108" r="84"></circle><circle cx="108" cy="108" r="84"></circle></svg>
                            <div class="number">
                                <h2>{{ $sum['rooms'] ? $sum['measured_percent'] . '%' : '—' }}</h2>
                                <p>{!! $sum['rooms'] ? 'замерено<br>помещений' : 'замеров<br>нет' !!}</p>
                            </div>
                        </div>
                    </div>
                    <div class="details-with-toggle">
                        <div class="details-with-toggle__wrap">
                            <div class="details-with-toggle__stats-row row">
                                <div class="details-with-toggle__col col-md-3 col-xl"><div class="details-with-toggle__item"><span>Смета с НДС</span><p>{{ $fmt($e['total']) }} ₽</p></div></div>
                                <div class="details-with-toggle__col col-md-3 col-xl"><div class="details-with-toggle__item"><span>Работы</span><p>{{ $fmt($e['work']) }} ₽</p></div></div>
                                <div class="details-with-toggle__col col-md-3 col-xl"><div class="details-with-toggle__item"><span>Материалы</span><p>{{ $fmt($e['materials']) }} ₽</p></div></div>
                                <div class="details-with-toggle__col col-md-3 col-xl"><div class="details-with-toggle__item"><span>Вариантов</span><p>{{ $sum['variants'] }}</p></div></div>
                            </div>
                            <div class="details-with-toggle__stats-row details-with-toggle__stats-row--without-span row">
                                @if ($sum['rooms'])
                                <div class="details-with-toggle__col col-md-3 col-xl"><div class="details-with-toggle__item"><p>{{ $sum['buildings'] }} дом</p></div></div>
                                <div class="details-with-toggle__col col-md-3 col-xl"><div class="details-with-toggle__item"><p>{{ $sum['entrances'] }} под</p></div></div>
                                <div class="details-with-toggle__col col-md-3 col-xl"><div class="details-with-toggle__item"><p>{{ $sum['apartments'] }} кв</p></div></div>
                                <div class="details-with-toggle__col col-md-3 col-xl"><div class="details-with-toggle__item"><p>{{ $sum['rooms'] }} пом</p></div></div>
                                @else
                                    <div class="details-with-toggle__col col-md-6 col-xl-auto"><div class="details-with-toggle__item"><p>{{ $sum['lines'] ? 'смета по объёмам без замера' : 'помещений пока нет' }}</p></div></div>
                                @endif
                                <div class="details-with-toggle__col col-md-3 col-xl"><div class="details-with-toggle__item"><p>S {{ $fmt($e['area']) }} м²</p></div></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sPageInfo__col col-md-5 col-xxl-auto">
                    <div class="sPageInfo__inner-row row">
                        <div class="col-12 crm-shares-title">Доля в смете</div>
                        @forelse ($sum['shares'] as $s)
                            <div class="col-12">
                                <div class="work-status">
                                    <div class="row"><div class="col">{{ $s['name'] }}</div><div class="col-auto fw-500">{{ $s['priced'] ? $s['percent'] . '%' : 'нет цены' }}</div></div>
                                    <div class="line" style="--w: {{ $s['percent'] }}%; --bgColor: {{ $s['color'] }}"></div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 crm-shares-title">Виды работ ещё не выбраны</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
