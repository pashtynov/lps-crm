@extends('layouts.app', ['title' => $object->name . ' — CRM Лидерпрофстрой', 'bodyClass' => 'object-page'])

@php
    $fmt = fn ($v, $d = 2) => number_format((float) $v, $d, ',', ' ');
    $sprite = asset('verstka/img/svg/sprite.svg');
    $workTypes = \App\Calc\Catalog::workTypes();
@endphp

@section('content')
    @include('objects._hero')

    <div class="sMainInfo section" id="sMainInfo">
        <div class="container-fluid">
            @include('objects._tabs', ['active' => 'list'])

            <div class="main-table crm-rooms">
                <table>
                    <thead>
                    <tr><th>Помещение</th><th>Под</th><th>Эт</th><th>Кв</th><th>Пол, м²</th><th>Стены, м²</th><th>Периметр, м</th><th>Работы</th><th></th></tr>
                    </thead>
                    <tbody>
                    <tr><td class="add-more" colspan="9">
                        <a class="main-table__add-new" href="{{ route('rooms.create', $object) }}">
                            <svg class="icon icon-add"><use xlink:href="{{ $sprite }}#add"></use></svg>Добавить помещение
                        </a>
                    </td></tr>
                    @php($section = null)
                    @foreach ($rooms as $i => ['room' => $r, 'g' => $g])
                        @if ($r->section !== $section)
                            @php($section = $r->section)
                            <tr class="crm-section-row"><td colspan="9"><span class="h3">{{ $section }}</span></td></tr>
                        @endif
                        <tr>
                            <td>
                                <div class="main-table__title">
                                    <a href="{{ route('rooms.edit', [$object, $r]) }}">{{ $r->name }}</a>
                                </div>
                                <div class="text-secondary small">{{ \App\Models\Room::KINDS[$r->kind] ?? '' }}@if ($r->building) · дом {{ $r->building }}@endif</div>
                            </td>
                            <td>{{ $r->entrance ?: '—' }}</td>
                            <td>{{ $r->floor ?: '—' }}</td>
                            <td>{{ $r->apartment ?: '—' }}</td>
                            <td>{{ $fmt($g['floor']) }}</td>
                            <td>{{ $fmt($g['walls']) }}</td>
                            <td>{{ $fmt($g['perimeter']) }}</td>
                            <td>
                                @forelse ($r->works as $w)
                                    <span class="crm-tag" title="{{ $workTypes[$w->work_type]['name'] ?? '' }}">{{ $workTypes[$w->work_type]['short'] ?? $w->work_type }}</span>
                                @empty
                                    <span class="text-secondary">не выбраны</span>
                                @endforelse
                            </td>
                            <td class="text-nowrap">
                                <form method="post" action="{{ route('rooms.copy', [$object, $r]) }}" class="d-inline">@csrf
                                    <button class="sCreateObject__control-btn btn btn-light" title="Копировать с замером и работами">
                                        <svg class="icon icon-copy"><use xlink:href="{{ $sprite }}#copy"></use></svg></button></form>
                                <form method="post" action="{{ route('rooms.delete', [$object, $r]) }}" class="d-inline" onsubmit="return confirm('Удалить помещение «{{ $r->name }}»?')">@csrf @method('DELETE')
                                    <button class="sCreateObject__control-btn sCreateObject__control-btn--delete btn btn-light" title="Удалить">
                                        <svg class="icon icon-delete"><use xlink:href="{{ $sprite }}#delete"></use></svg></button></form>
                            </td>
                        </tr>
                    @endforeach
                    @if ($rooms->isEmpty())
                        <tr><td colspan="9" class="text-secondary">
                            Помещений нет@if ($object->lines->isNotEmpty()) — смета этого объекта собрана по готовым объёмам ({{ $object->lines->count() }} поз.), см. вкладку «Смета»@endif.
                        </td></tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
