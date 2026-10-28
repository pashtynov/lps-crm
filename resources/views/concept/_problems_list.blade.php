{{-- Список проблем в области (объект / дом / подъезд), ссылки на квартиры --}}
@include('concept._problems_js')
<div class="crm-problems" data-problems-list data-house="{{ $house ?? '' }}" data-section="{{ $section ?? '' }}" hidden></div>
