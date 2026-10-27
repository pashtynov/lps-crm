@php
    $fmt = fn ($v, $d = 2) => number_format((float) $v, $d, ',', ' ');
    $sprite = asset('verstka/img/svg/sprite.svg');
@endphp
<div>
    <form wire:submit="save" class="sCreateObject">
        <div class="sCreateObject__body">
            <div class="sCreateObject__left-side">
                <h2>{{ $roomId ? 'Редактирование помещения' : 'Новое помещение' }}</h2>
                <p class="crm-crumb"><a href="{{ route('objects.show', $object) }}">{{ $object->name }}</a></p>
                <div class="form-wrap">
                    <div class="form-wrap__main-row row">
                        <div class="col-12">
                            <div class="custom-input-wrap form-group">
                                <input class="custom-input-wrap__input form-control" wire:model="name" placeholder="Введите название: кухня, комната 1, МОП 3 этажа">
                                <div class="custom-input-wrap__line"></div>
                            </div>
                            @error('name')<div class="crm-error">{{ $message }}</div>@enderror
                        </div>

                        {{-- Чертёж и площади — plan-block из макета; рисунок по способу замера --}}
                        <div class="col-12">
                            <div class="plan-block">
                                <div class="crm-plan">
                                    @if ($shape === 'l')
                                        <svg viewBox="0 0 220 190"><path d="M20 15 H120 V75 H200 V175 H20 Z" fill="#d9d9d9" stroke="#8b9298" stroke-width="4"/>
                                            <text x="4" y="98">A</text><text x="66" y="11">B</text><text x="126" y="48">C</text><text x="154" y="70">D</text><text x="205" y="128">E</text><text x="104" y="189">F</text></svg>
                                    @elseif ($shape === 'rect')
                                        <svg viewBox="0 0 220 190"><rect x="20" y="15" width="180" height="160" fill="#d9d9d9" stroke="#8b9298" stroke-width="4"/>
                                            <text x="104" y="11">A</text><text x="4" y="98">B</text></svg>
                                    @else
                                        <svg viewBox="0 0 220 190"><path d="M20 15 H150 L200 70 V175 H60 L20 130 Z" fill="#d9d9d9" stroke="#8b9298" stroke-width="4"/>
                                            <text x="80" y="100" style="font-size:13px">S и P</text></svg>
                                    @endif
                                </div>
                                <div class="plan-block__info">
                                    <p class="row"><span class="col">Площадь пола</span><span class="col-auto">{{ $fmt($g['floor']) }}</span></p>
                                    <p class="row"><span class="col">Площадь стен</span><span class="col-auto">{{ $fmt($g['walls']) }}</span></p>
                                    <p class="row"><span class="col">Площадь потолка</span><span class="col-auto">{{ $fmt($g['ceiling']) }}</span></p>
                                    <p class="row"><span class="col">Периметр</span><span class="col-auto">{{ $fmt($g['perimeter']) }}</span></p>
                                    <p class="row"><span class="col">Проёмы</span><span class="col-auto">{{ $fmt($g['openings_area']) }}</span></p>
                                    <p class="row"><span class="col">Пол + порог (стяжка)</span><span class="col-auto">{{ $fmt($g['floor_threshold'], 3) }}</span></p>
                                    <p class="row"><span class="col">Периметр без дверей</span><span class="col-auto">{{ $fmt($g['perimeter_net']) }}</span></p>
                                    @if ($shape === 'l')<p class="crm-hint">B = F − D, E = A − C, периметр всегда 2·(A + F)</p>@endif
                                    @if ($zeroArea)
                                        <div class="crm-warning mt-2">Нулевая площадь для: {{ implode(', ', $zeroArea) }}. Введите размеры и высоту.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <h4 class="crm-h4">Замер</h4>
                            <div class="crm-tabs">
                                @foreach (\App\Models\Room::SHAPES as $k => $label)
                                    <label class="crm-tab {{ $shape === $k ? 'active' : '' }}"><input type="radio" wire:model.live="shape" value="{{ $k }}" hidden>{{ $label }}</label>
                                @endforeach
                            </div>
                            <div class="row g-3">
                                @if ($shape === 'rect')
                                    <div class="col-6 col-md-3"><label class="crm-label">Длина A, м</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="a"></div>
                                    <div class="col-6 col-md-3"><label class="crm-label">Ширина B, м</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="b"></div>
                                @elseif ($shape === 'l')
                                    <div class="col-6 col-md-3"><label class="crm-label">A — левая стена</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="a"></div>
                                    <div class="col-6 col-md-3"><label class="crm-label">F — нижняя стена</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="f"></div>
                                    <div class="col-6 col-md-3"><label class="crm-label">C — выступ, высота</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="c"></div>
                                    <div class="col-6 col-md-3"><label class="crm-label">D — выступ, ширина</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="d"></div>
                                @else
                                    <div class="col-6 col-md-3"><label class="crm-label">Площадь пола, м²</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="floorArea"></div>
                                    <div class="col-6 col-md-3"><label class="crm-label">Периметр, м</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="perimeter"></div>
                                @endif
                                <div class="col-6 col-md-3"><label class="crm-label">Высота H, м</label><input class="form-control" inputmode="decimal" wire:model.live.debounce.400ms="height">
                                    @error('height')<div class="crm-error">{{ $message }}</div>@enderror</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <h4 class="crm-h4">Проёмы</h4>
                            <table class="crm-table">
                                <thead><tr><th>Тип</th><th>Ширина, м</th><th>Высота, м</th><th>Стена, м</th><th>Порог</th><th></th></tr></thead>
                                <tbody>
                                @foreach ($openings as $i => $o)
                                    <tr wire:key="op-{{ $i }}">
                                        <td><select class="form-select form-select-sm" wire:model.live="openings.{{ $i }}.type">
                                                <option value="ДВ">ДВ — дверь</option><option value="ОК">ОК — окно</option><option value="ПР">ПР — проём</option></select></td>
                                        <td><input class="form-control form-control-sm" inputmode="decimal" wire:model.live.debounce.400ms="openings.{{ $i }}.width"></td>
                                        <td><input class="form-control form-control-sm" inputmode="decimal" wire:model.live.debounce.400ms="openings.{{ $i }}.height"></td>
                                        <td>@if ($o['type'] !== 'ОК')<input class="form-control form-control-sm" inputmode="decimal" wire:model.live.debounce.400ms="openings.{{ $i }}.wall">@endif</td>
                                        <td>@if ($o['type'] !== 'ОК')<input type="checkbox" class="form-check-input" wire:model.live="openings.{{ $i }}.threshold">@endif</td>
                                        <td><button type="button" class="btn btn-light btn-sm" wire:click="removeOpening({{ $i }})" title="Убрать">✕</button></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-light btn-sm" wire:click="addOpening('ДВ')">+ дверь</button>
                                <button type="button" class="btn btn-light btn-sm" wire:click="addOpening('ОК')">+ окно</button>
                                <button type="button" class="btn btn-light btn-sm" wire:click="addOpening('ПР')">+ проём</button>
                            </div>
                        </div>

                        <div class="col-12">
                            <h4 class="crm-h4">Виды работ</h4>
                            @foreach (collect($workTypes)->groupBy('surface') as $surface => $list)
                                <p class="crm-label mt-2">{{ \App\Calc\Catalog::SURFACES[$surface] ?? $surface }}</p>
                                @foreach ($list as $wt)
                                    <div class="crm-work {{ $works[$wt['code']]['on'] ? 'on' : '' }}" wire:key="w-{{ $wt['code'] }}">
                                        <label class="crm-work__label">
                                            <input type="checkbox" class="form-check-input" wire:model.live="works.{{ $wt['code'] }}.on">
                                            <span class="crm-work__name"><b>{{ $wt['short'] }}</b><small>{{ $wt['name'] }}</small></span>
                                        </label>
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
                </div>
            </div>

            <div class="sCreateObject__right-side">
                <div class="sCreateObject__row row">
                    <div class="col-12">
                        <div class="sCreateObject__item">
                            <p>Тип помещения</p>
                            <select class="form-select" wire:model="kind">
                                @foreach (\App\Models\Room::KINDS as $k => $label)<option value="{{ $k }}">{{ $label }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-6"><div class="sCreateObject__item"><p>Дом</p><input class="form-control" wire:model="building"></div></div>
                    <div class="col-6"><div class="sCreateObject__item"><p>Подъезд</p><input class="form-control" wire:model="entrance"></div></div>
                    <div class="col-6"><div class="sCreateObject__item"><p>Этаж</p><input class="form-control" wire:model="floor"></div></div>
                    <div class="col-6"><div class="sCreateObject__item"><p>Квартира</p><input class="form-control" wire:model="apartment"></div></div>
                    <div class="col-12">
                        <details class="crm-more" @if ($errors->has('section')) open @endif>
                            <summary>Дополнительно</summary>
                            <div class="sCreateObject__item mt-2">
                                <p>Раздел сметы</p>
                                <input class="form-control" wire:model="section" list="sections">
                                <datalist id="sections">
                                    @foreach ($sections as $s)<option value="{{ $s }}">@endforeach
                                    <option value="Отделка помещений ниже отм. 0,000"><option value="Отделка помещений выше отм. 0,000">
                                </datalist>
                                <div class="crm-hint">Заголовок группы в смете. Нужен, когда смету делят на части — например, подвал (ниже отм. 0,000) и этажи. Обычно не меняют: новое помещение берёт раздел предыдущего.</div>
                                @error('section')<div class="crm-error">{{ $message }}</div>@enderror
                            </div>
                        </details>
                    </div>
                    @if ($room)
                        <div class="col-12"><div class="sCreateObject__item"><p>Дата создания</p><span>{{ $room->created_at->format('d.m.Y, H:i') }}</span></div></div>
                        <div class="col-12"><div class="sCreateObject__item"><p>Последнее изменение</p><span>{{ $room->updated_at->format('d.m.Y, H:i') }}</span></div></div>
                        <div class="col-12">
                            <div class="sCreateObject__btn-row row">
                                <div class="col-auto">
                                    <button type="submit" form="room-copy" class="sCreateObject__control-btn btn btn-light" title="Дублировать с замером и работами">
                                        <svg class="icon icon-copy"><use xlink:href="{{ $sprite }}#copy"></use></svg></button>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" form="room-delete" class="sCreateObject__control-btn sCreateObject__control-btn--delete btn btn-light" title="Удалить"
                                            onclick="return confirm('Удалить помещение?')">
                                        <svg class="icon icon-delete"><use xlink:href="{{ $sprite }}#delete"></use></svg></button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="sCreateObject__footer">
            <div class="sCreateObject__footer-row">
                @error('area')<div class="crm-error w-100 mb-2">{{ $message }}</div>@enderror
                <button type="submit" class="sCreateObject__btn btn btn-accent">Сохранить</button>
                <button type="button" class="sCreateObject__btn btn btn-light" wire:click="saveAndNext" title="Сохранить и сразу завести следующее помещение той же квартиры">Сохранить и следующее</button>
                <a class="sCreateObject__btn btn btn-light" href="{{ route('objects.show', $object) }}">Отмена</a>
            </div>
        </div>
    </form>
    @if ($room)
        <form id="room-copy" method="post" action="{{ route('rooms.copy', [$object, $room]) }}">@csrf</form>
        <form id="room-delete" method="post" action="{{ route('rooms.delete', [$object, $room]) }}">@csrf @method('DELETE')</form>
    @endif
    <a class="close-btn" href="{{ route('objects.show', $object) }}" title="Закрыть"><svg class="icon icon-close"><use xlink:href="{{ $sprite }}#close"></use></svg></a>
</div>
