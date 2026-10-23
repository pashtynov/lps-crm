@extends('layouts.app', ['title' => $object->name . ' — CRM Лидерпрофстрой'])

@php($fmt = fn ($v, $d = 2) => number_format((float) $v, $d, ',', ' '))

@section('content')
    @include('partials.page-head', [
        'heading' => $object->name,
        'badge' => $object->statusLabel(),
        'crumbs' => [$object->name => route('objects.show', $object)],
        'actions' => '<a class="btn btn-accent" href="' . route('estimate', $object) . '">Смета по объекту</a>',
    ])

    <div class="sObjectDetails section">
        <div class="container-fluid">
            <div class="details-with-toggle__stats-row details-with-toggle__stats-row--without-span row">
                <div class="details-with-toggle__col col-md-auto"><div class="details-with-toggle__item"><span>Помещения</span><p>{{ $rooms->count() }}</p></div></div>
                <div class="details-with-toggle__col col-md-auto"><div class="details-with-toggle__item"><span>Пол</span><p>{{ $fmt($totals['floor']) }} м²</p></div></div>
                <div class="details-with-toggle__col col-md-auto"><div class="details-with-toggle__item"><span>Стены</span><p>{{ $fmt($totals['walls']) }} м²</p></div></div>
                <div class="details-with-toggle__col col-md-auto"><div class="details-with-toggle__item"><span>Потолок</span><p>{{ $fmt($totals['ceiling']) }} м²</p></div></div>
                @if ($object->lines->isNotEmpty())
                    <div class="details-with-toggle__col col-md-auto"><div class="details-with-toggle__item"><span>Позиций с готовым объёмом</span><p>{{ $object->lines->count() }}</p></div></div>
                @endif
            </div>
            @if ($object->address || $object->customer)
                <p class="text-secondary mt-2">{{ collect([$object->address, $object->customer])->filter()->implode(' · ') }}</p>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="container-fluid">
            <div class="main-table main-table--2">
                <table>
                    <thead>
                    <tr><th>Помещение</th><th>Где</th><th>Пол, м²</th><th>Стены, м²</th><th>Периметр, м</th><th>Работы</th><th></th></tr>
                    </thead>
                    <tbody>
                    <tr><td class="add-more" colspan="7">
                        <a class="main-table__add-new" href="{{ route('rooms.create', $object) }}">
                            <svg class="icon"><use xlink:href="{{ asset('verstka/img/svg/sprite.svg') }}#add"></use></svg>Добавить помещение
                        </a>
                    </td></tr>
                    @php($section = null)
                    @foreach ($rooms as ['room' => $r, 'g' => $g])
                        @if ($r->section !== $section)
                            @php($section = $r->section)
                            <tr class="crm-section-row"><td colspan="7">{{ $section }}</td></tr>
                        @endif
                        <tr>
                            <td><div class="main-table__title"><a href="{{ route('rooms.edit', [$object, $r]) }}">{{ $r->name }}</a></div>
                                <div class="text-secondary small">{{ \App\Models\Room::KINDS[$r->kind] ?? '' }}</div></td>
                            <td>{{ $r->place() }}</td>
                            <td>{{ $fmt($g['floor']) }}</td>
                            <td>{{ $fmt($g['walls']) }}</td>
                            <td>{{ $fmt($g['perimeter']) }}</td>
                            <td class="small">{{ $r->works->map(fn ($w) => \App\Calc\Catalog::workTypes()[$w->work_type]['group'] ?? $w->work_type)->unique()->implode(', ') ?: '—' }}</td>
                            <td class="text-nowrap">
                                <form method="post" action="{{ route('rooms.copy', [$object, $r]) }}" class="d-inline">@csrf
                                    <button class="btn btn-light btn-sm" title="Копировать с замером и работами">Копия</button></form>
                                <form method="post" action="{{ route('rooms.delete', [$object, $r]) }}" class="d-inline" onsubmit="return confirm('Удалить помещение «{{ $r->name }}»?')">@csrf @method('DELETE')
                                    <button class="btn btn-light btn-sm">✕</button></form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if ($object->estimates->isNotEmpty())
                <h3 class="mt-4">Сохранённые варианты сметы</h3>
                <ul class="crm-list">
                    @foreach ($object->estimates as $e)
                        <li>{{ $e->title }} — {{ $fmt($e->result['total']) }} ₽ ·
                            <a href="{{ route('estimates.xlsx', $e) }}">Excel</a> · <a href="{{ route('estimates.pdf', $e) }}" target="_blank">PDF</a></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
