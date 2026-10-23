@php($fmt = fn ($v, $d = 2) => $v === null ? '—' : number_format((float) $v, $d, ',', ' '))
<div>
    @include('partials.page-head', [
        'heading' => 'Смета',
        'crumbs' => [$object->name => route('objects.show', $object), 'Смета' => route('estimate', $object)],
        'actions' => '<a class="btn btn-light" href="' . route('estimate.xlsx', $object) . '">Excel</a> <a class="btn btn-light" target="_blank" href="' . route('estimate.pdf', $object) . '">PDF</a>',
    ])

    <div class="section">
        <div class="container-fluid">
            <div class="crm-total">
                <div><span>Сметная стоимость с НДС</span><b>{{ $fmt($result['total']) }} ₽</b></div>
                <div><span>Строительные работы</span><b>{{ $fmt($result['work']) }} ₽</b></div>
                <div><span>Материалы</span><b>{{ $fmt($result['materials']) }} ₽</b></div>
                <div><span>в т.ч. НДС
                        <input class="crm-inline" inputmode="decimal" wire:model.live.debounce.600ms="vatPercent"> %</span>
                    <b>{{ $fmt($result['vat']) }} ₽</b></div>
                <div><span>Площадь работ</span><b>{{ $fmt($result['area']) }} м²</b></div>
            </div>

            @foreach ($result['warnings'] as $w)
                <div class="crm-warning">{{ $w }}</div>
            @endforeach

            <div class="main-table crm-estimate">
                <table>
                    <thead>
                    <tr><th>№</th><th>Наименование работ и затрат</th><th>Ед.</th><th>Кол-во</th>
                        <th>Цена работы</th><th>Цена мат.</th><th>Стоимость работ</th><th>Стоимость мат.</th></tr>
                    </thead>
                    <tbody>
                    @php($n = 0)
                    @foreach ($result['sections'] as $s)
                        <tr class="crm-section-row"><td colspan="8">{{ $s['name'] }}
                            <span class="crm-waste">мусор, контейнеров 8 м³:
                                <input class="crm-inline" inputmode="decimal" wire:model.live.debounce.600ms="waste.{{ md5($s['name']) }}"></span></td></tr>
                        @foreach ($s['lines'] as $l)
                            <tr class="crm-work-row">
                                <td>{{ ++$n }}</td><td>{{ $l['name'] }}</td><td>{{ $l['unit'] === 'м2' ? 'м²' : $l['unit'] }}</td>
                                <td class="num">{{ $fmt($l['qty']) }}</td><td class="num">{{ $fmt($l['price']) }}</td><td></td>
                                <td class="num">{{ $fmt($l['work_sum']) }}</td><td></td>
                            </tr>
                            @foreach ($l['materials'] as $m)
                                <tr class="crm-mat-row">
                                    <td></td><td>{{ $m['name'] }}
                                        @if ($m['packs'])<span class="text-secondary"> · {{ $fmt($m['packs'], 0) }} {{ $m['pack_name'] }}</span>@endif</td>
                                    <td>{{ $m['unit'] === 'м2' ? 'м²' : $m['unit'] }}</td>
                                    <td class="num" title="точно {{ $fmt($m['exact'], 3) }}">{{ $fmt($m['qty'], $m['qty'] == floor((float) $m['qty']) ? 0 : 1) }}</td>
                                    <td></td><td class="num">{{ $fmt($m['price']) }}</td><td></td><td class="num">{{ $fmt($m['sum']) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                        @foreach ($s['extras'] as $e)
                            <tr class="crm-work-row"><td>{{ ++$n }}</td><td>{{ $e['name'] }}</td><td>{{ $e['unit'] }}</td>
                                <td class="num">{{ $fmt($e['qty'], 0) }}</td><td class="num">{{ $fmt($e['price']) }}</td><td></td>
                                <td class="num">{{ $fmt($e['sum']) }}</td><td></td></tr>
                        @endforeach
                        <tr class="crm-subtotal"><td></td><td colspan="5">Итого: {{ $s['name'] }}</td>
                            <td class="num">{{ $fmt($s['work']) }}</td><td class="num">{{ $fmt($s['materials']) }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row g-4 mt-1">
                <div class="col-lg-7">
                    <div class="crm-card">
                        <h3>Позиции с готовым объёмом</h3>
                        <p class="text-secondary small">Когда замеров нет, а объём известен из проекта или ведомости. Площади из замеров помещений добавляются в смету сами.</p>
                        @if ($lines->isNotEmpty())
                            <table class="crm-table">
                                @foreach ($lines as $l)
                                    <tr wire:key="l-{{ $l->id }}"><td class="small">{{ $l->section }}</td><td class="small">{{ $workTypes[$l->work_type]['name'] ?? $l->work_type }}</td>
                                        <td class="num">{{ $fmt($l->qty) }}</td>
                                        <td><button class="btn btn-light btn-sm" wire:click="removeLine({{ $l->id }})" wire:confirm="Убрать позицию?">✕</button></td></tr>
                                @endforeach
                            </table>
                        @endif
                        <form wire:submit="addLine" class="row g-2">
                            <div class="col-md-4"><input class="form-control" wire:model="newSection" list="est-sections" placeholder="Раздел">
                                <datalist id="est-sections">@foreach ($sections as $s)<option value="{{ $s }}">@endforeach</datalist></div>
                            <div class="col-md-5"><select class="form-select" wire:model="newWorkType">
                                    @foreach ($workTypes as $code => $wt)<option value="{{ $code }}">{{ $wt['name'] }}</option>@endforeach</select></div>
                            <div class="col-md-2"><input class="form-control" inputmode="decimal" wire:model="newQty" placeholder="м²">
                                @error('newQty')<div class="crm-error">{{ $message }}</div>@enderror</div>
                            <div class="col-md-1"><button class="btn btn-accent w-100">+</button></div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="crm-card">
                        <h3>Варианты</h3>
                        <form wire:submit="saveVariant" class="d-flex gap-2 mb-3">
                            <input class="form-control" wire:model="variantTitle" placeholder="Название, например «для заказчика 28.10»">
                            <button class="btn btn-accent text-nowrap">Сохранить вариант</button>
                        </form>
                        <ul class="crm-list">
                            @forelse ($variants as $v)
                                <li>{{ $v->title }} — <b>{{ $fmt($v->result['total']) }} ₽</b>
                                    <span class="text-secondary small">{{ $v->created_at->format('d.m H:i') }}</span> ·
                                    <a href="{{ route('estimates.xlsx', $v) }}">Excel</a> · <a href="{{ route('estimates.pdf', $v) }}" target="_blank">PDF</a></li>
                            @empty
                                <li class="text-secondary">Сохранённых вариантов пока нет. Вариант — снимок с текущими ценами: поменяете цены — старый вариант не изменится.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
