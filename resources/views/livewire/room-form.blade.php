@php($fmt = fn ($v, $d = 2) => number_format((float) $v, $d, ',', ' '))
<div>
    @include('partials.page-head', [
        'heading' => $roomId ? ($name ?: 'Помещение') : 'Новое помещение',
        'crumbs' => [$object->name => route('objects.show', $object), ($roomId ? $name : 'Новое помещение') => '#'],
    ])

    <form wire:submit="save" class="section">
        <div class="container-fluid">
            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="crm-card">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="crm-label">Название</label>
                                <input class="form-control" wire:model="name" placeholder="Кухня, комната 1, МОП 3 этажа">
                                @error('name')<div class="crm-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="crm-label">Тип</label>
                                <select class="form-select" wire:model="kind">
                                    @foreach (\App\Models\Room::KINDS as $k => $label)<option value="{{ $k }}">{{ $label }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="crm-label">Раздел сметы</label>
                                <input class="form-control" wire:model="section" list="sections">
                                <datalist id="sections">
                                    @foreach ($sections as $s)<option value="{{ $s }}">@endforeach
                                    <option value="Отделка помещений ниже отм. 0,000">
                                    <option value="Отделка помещений выше отм. 0,000">
                                </datalist>
                            </div>
                            <div class="col-6 col-md-3"><label class="crm-label">Дом</label><input class="form-control" wire:model="building"></div>
                            <div class="col-6 col-md-3"><label class="crm-label">Подъезд</label><input class="form-control" wire:model="entrance"></div>
                            <div class="col-6 col-md-3"><label class="crm-label">Этаж</label><input class="form-control" wire:model="floor"></div>
                            <div class="col-6 col-md-3"><label class="crm-label">Квартира</label><input class="form-control" wire:model="apartment"></div>
                        </div>
                    </div>

                    <div class="crm-card">
                        <h3>Замер</h3>
                        <div class="crm-tabs">
                            @foreach (\App\Models\Room::SHAPES as $k => $label)
                                <label class="crm-tab {{ $shape === $k ? 'active' : '' }}"><input type="radio" wire:model.live="shape" value="{{ $k }}" hidden>{{ $label }}</label>
                            @endforeach
                        </div>

                        <div class="row g-3 align-items-start">
                            <div class="col-md-7">
                                <div class="row g-3">
                                    @if ($shape === 'rect')
                                        <div class="col-6"><label class="crm-label">Длина A, м</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="a"></div>
                                        <div class="col-6"><label class="crm-label">Ширина B, м</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="b"></div>
                                    @elseif ($shape === 'l')
                                        <div class="col-6"><label class="crm-label">A — левая стена, м</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="a"></div>
                                        <div class="col-6"><label class="crm-label">F — нижняя стена, м</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="f"></div>
                                        <div class="col-6"><label class="crm-label">C — выступ по высоте, м</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="c"></div>
                                        <div class="col-6"><label class="crm-label">D — выступ по ширине, м</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="d"></div>
                                    @else
                                        <div class="col-6"><label class="crm-label">Площадь пола, м²</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="floorArea"></div>
                                        <div class="col-6"><label class="crm-label">Периметр, м</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="perimeter"></div>
                                    @endif
                                    <div class="col-6"><label class="crm-label">Высота H, м</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="height">
                                        @error('height')<div class="crm-error">{{ $message }}</div>@enderror</div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                @if ($shape === 'l')
                                    {{-- схема из файла формул клиента: габарит A × F минус вырез C × D --}}
                                    <svg viewBox="0 0 220 170" class="crm-scheme">
                                        <path d="M30 20 H130 V70 H190 V150 H30 Z" fill="#f4f6f8" stroke="#2a3137" stroke-width="2"/>
                                        <text x="14" y="88">A</text><text x="78" y="14">B</text><text x="136" y="48">C</text>
                                        <text x="158" y="64">D</text><text x="196" y="114">E</text><text x="106" y="166">F</text>
                                    </svg>
                                    <p class="text-secondary small">B = F − D, E = A − C. Периметр Г-образной комнаты всегда 2·(A + F).</p>
                                @elseif ($shape === 'rect')
                                    <svg viewBox="0 0 220 170" class="crm-scheme">
                                        <rect x="30" y="20" width="160" height="130" fill="#f4f6f8" stroke="#2a3137" stroke-width="2"/>
                                        <text x="14" y="88">B</text><text x="106" y="14">A</text>
                                    </svg>
                                @else
                                    <p class="text-secondary small">Для помещений сложной формы или по данным проекта: площадь пола и периметр вводятся как есть, стены считаются как периметр × высота минус проёмы.</p>
                                @endif
                            </div>
                        </div>

                        <h4 class="mt-4">Проёмы</h4>
                        <table class="crm-table">
                            <thead><tr><th>Тип</th><th>Ширина, м</th><th>Высота, м</th><th>Толщина стены, м</th><th>Порог</th><th></th></tr></thead>
                            <tbody>
                            @foreach ($openings as $i => $o)
                                <tr wire:key="op-{{ $i }}">
                                    <td><select class="form-select form-select-sm" wire:model.live="openings.{{ $i }}.type">
                                            <option value="ДВ">ДВ — дверь</option><option value="ОК">ОК — окно</option><option value="ПР">ПР — проём</option></select></td>
                                    <td><input class="form-control form-control-sm" inputmode="decimal" wire:model.live.debounce.400ms="openings.{{ $i }}.width"></td>
                                    <td><input class="form-control form-control-sm" inputmode="decimal" wire:model.live.debounce.400ms="openings.{{ $i }}.height"></td>
                                    <td>@if ($o['type'] !== 'ОК')<input class="form-control form-control-sm" inputmode="decimal" wire:model.live.debounce.400ms="openings.{{ $i }}.wall">@endif</td>
                                    <td>@if ($o['type'] !== 'ОК')<input type="checkbox" class="form-check-input" wire:model.live="openings.{{ $i }}.threshold">@endif</td>
                                    <td><button type="button" class="btn btn-light btn-sm" wire:click="removeOpening({{ $i }})">✕</button></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light btn-sm" wire:click="addOpening('ДВ')">+ дверь</button>
                            <button type="button" class="btn btn-light btn-sm" wire:click="addOpening('ОК')">+ окно</button>
                            <button type="button" class="btn btn-light btn-sm" wire:click="addOpening('ПР')">+ проём</button>
                        </div>
                    </div>

                    <div class="crm-card">
                        <h3>Виды работ в помещении</h3>
                        @foreach (collect($workTypes)->groupBy('surface') as $surface => $list)
                            <h4>{{ \App\Calc\Catalog::SURFACES[$surface] ?? $surface }}</h4>
                            @foreach ($list as $wt)
                                <div class="crm-work {{ $works[$wt['code']]['on'] ? 'on' : '' }}" wire:key="w-{{ $wt['code'] }}">
                                    <label><input type="checkbox" class="form-check-input" wire:model.live="works.{{ $wt['code'] }}.on"> {{ $wt['name'] }}</label>
                                    @if ($works[$wt['code']]['on'] && array_key_exists('thickness', $works[$wt['code']]))
                                        <span class="crm-param">слой, мм <input class="form-control form-control-sm" inputmode="decimal" wire:model.live.debounce.400ms="works.{{ $wt['code'] }}.thickness"></span>
                                    @endif
                                    @if ($works[$wt['code']]['on'] && array_key_exists('layers', $works[$wt['code']]))
                                        <span class="crm-param">слоёв <input class="form-control form-control-sm" inputmode="numeric" wire:model.live.debounce.400ms="works.{{ $wt['code'] }}.layers"></span>
                                    @endif
                                    @if ($wt['price'] === null)<span class="crm-note">цену работы уточнить</span>@endif
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="crm-card crm-sticky">
                        <h3>Площади</h3>
                        <p class="crm-kv"><span>Периметр</span><b>{{ $fmt($g['perimeter']) }} м</b></p>
                        <p class="crm-kv"><span>Пол = потолок</span><b>{{ $fmt($g['floor']) }} м²</b></p>
                        <p class="crm-kv"><span>Стены за вычетом проёмов</span><b>{{ $fmt($g['walls']) }} м²</b></p>
                        <p class="crm-kv"><span>Проёмы</span><b>{{ $fmt($g['openings_area']) }} м²</b></p>
                        <p class="crm-kv"><span>Пол + порог (стяжка)</span><b>{{ $fmt($g['floor_threshold'], 3) }} м²</b></p>
                        <p class="crm-kv"><span>Периметр без дверей (плинтус)</span><b>{{ $fmt($g['perimeter_net']) }} м</b></p>
                        <div class="d-grid gap-2 mt-4">
                            <button class="btn btn-accent">Сохранить</button>
                            <a class="btn btn-light" href="{{ route('objects.show', $object) }}">Отмена</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
