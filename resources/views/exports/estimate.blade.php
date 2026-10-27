@php($fmt = fn ($v, $d = 2) => $v === null ? '' : number_format((float) $v, $d, ',', ' '))
<style>
    body { font-family: dejavusans; font-size: 8.5pt; color: #222; }
    h1 { font-size: 13pt; margin: 0 0 2mm; } .muted { color: #666; }
    table { width: 100%; border-collapse: collapse; margin-top: 4mm; }
    th, td { border: 0.2mm solid #999; padding: 1.2mm 1.5mm; vertical-align: top; }
    th { background: #eef1f4; font-weight: bold; }
    .num { text-align: right; white-space: nowrap; } .sec td { background: #f6f7f9; font-weight: bold; }
    .mat td { color: #444; } .sub td { font-weight: bold; }
    .totals td { border: none; padding: 0.8mm 1.5mm; font-size: 10pt; }
    .top td, .sign td { border: none; padding: 0; vertical-align: top; } .right { text-align: right; }
    .sign { margin-top: 5mm; font-size: 9pt; line-height: 1.5; }
    .title { text-align: center; margin: 6mm 0 2mm; } .title h1 { margin: 3mm 0 1mm; }
    .obj { font-size: 10pt; } .cap { font-size: 7.5pt; color: #777; border-top: 0.2mm solid #999; margin: 0.5mm 25% 0; }
    .note { margin: 0.5mm 0; }
</style>
<table class="top"><tr>
    <td style="width:30%"><img src="{{ resource_path('pdf/logo-lps.svg') }}" style="height:16mm"></td>
    <td class="right muted">Приложение № 1 к договору подряда № ________ от «____» ______________ 20___ г.</td>
</tr></table>

<table class="sign"><tr>
    <td style="width:50%"><b>СОГЛАСОВАНО:</b><br>{{ config('crm.director_title') }} {{ config('crm.company') }}<br><br>_________________ {{ config('crm.director') }}<br>«____» ______________ 20___ г.</td>
    <td><b>УТВЕРЖДАЮ:</b><br>{{ $object->customer ?: 'Заказчик' }}<br><br>_________________ /________________/<br>«____» ______________ 20___ г.</td>
</tr></table>

<div class="title">
    <div class="obj">«{{ $object->name }}»@if ($object->address), {{ $object->address }}@endif</div>
    <div class="cap">(наименование объекта)</div>
    <h1>СМЕТНЫЙ РАСЧЁТ</h1>
    <div class="obj">{{ collect($r['sections'])->flatMap(fn ($s) => collect($s['lines'])->pluck('group'))->filter()->unique()->implode('; ') ?: 'Отделочные работы' }}</div>
    <div class="cap">(наименование работ и затрат) · {{ $title }}</div>
</div>

<table>
    <thead>
    <tr><th rowspan="2">№</th><th rowspan="2">Наименование работ и затрат</th><th rowspan="2">Ед.</th><th rowspan="2">Кол-во</th>
        <th colspan="2">Цена, руб. с НДС</th><th colspan="2">Стоимость, руб. с НДС</th></tr>
    <tr><th>работа</th><th>материалы</th><th>работа</th><th>материалы</th></tr>
    </thead>
    <tbody>
    @php($n = 0)
    @foreach ($r['sections'] as $s)
        <tr class="sec"><td colspan="8">{{ $s['name'] }}</td></tr>
        @foreach ($s['lines'] as $l)
            <tr><td>{{ ++$n }}</td><td>{{ $l['name'] }}</td><td>{{ $l['unit'] === 'м2' ? 'м²' : $l['unit'] }}</td>
                <td class="num">{{ $fmt($l['qty']) }}</td><td class="num">{{ $fmt($l['price']) }}</td><td></td>
                <td class="num">{{ $fmt($l['work_sum']) }}</td><td></td></tr>
            @foreach ($l['materials'] as $m)
                <tr class="mat"><td></td><td>{{ $m['name'] }}</td><td>{{ $m['unit'] === 'м2' ? 'м²' : $m['unit'] }}</td>
                    <td class="num">{{ $fmt($m['qty'], $m['qty'] == floor((float) $m['qty']) ? 0 : 1) }}</td><td></td>
                    <td class="num">{{ $fmt($m['price']) }}</td><td></td><td class="num">{{ $fmt($m['sum']) }}</td></tr>
            @endforeach
        @endforeach
        @foreach ($s['extras'] as $e)
            <tr><td>{{ ++$n }}</td><td>{{ $e['name'] }}</td><td>{{ $e['unit'] }}</td><td class="num">{{ $fmt($e['qty'], 0) }}</td>
                <td class="num">{{ $fmt($e['price']) }}</td><td></td><td class="num">{{ $fmt($e['sum']) }}</td><td></td></tr>
        @endforeach
        <tr class="sub"><td></td><td colspan="5">Итого: {{ $s['name'] }}</td><td class="num">{{ $fmt($s['work']) }}</td><td class="num">{{ $fmt($s['materials']) }}</td></tr>
    @endforeach
    </tbody>
</table>

<table class="totals">
    <tr><td>Строительные работы, руб.</td><td class="num">{{ $fmt($r['work']) }}</td></tr>
    <tr><td>Материалы и оборудование, руб.</td><td class="num">{{ $fmt($r['materials']) }}</td></tr>
    <tr><td><b>ВСЕГО, руб.</b></td><td class="num"><b>{{ $fmt($r['total']) }}</b></td></tr>
    <tr><td>в т.ч. НДС {{ rtrim(rtrim(number_format((float) $r['vat_rate'] * 100, 2, ',', ''), '0'), ',') }}%</td><td class="num">{{ $fmt($r['vat']) }}</td></tr>
</table>
<p><b>Примечание:</b></p>
@foreach (config('crm.notes') as $i => $note)
    <p class="note">{{ $i + 1 }}. {{ $note }}</p>
@endforeach

<table class="sign" style="margin-top:8mm"><tr>
    <td style="width:50%">Составил ___________________________</td>
    <td>Проверил ___________________________</td>
</tr></table>
