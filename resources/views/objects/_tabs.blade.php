{{-- Вкладки объекта — toggle-block из макета. Задачи, график и заметки — следующие этапы --}}
@php($sprite = asset('verstka/img/svg/sprite.svg'))
<div class="sMainInfo__wrap">
    <div class="toggle-block">
        <div class="crm-tabs-row">
            <a class="toggle-block__btn btn btn-outline-light {{ $active === 'list' ? 'active' : '' }}" href="{{ route('objects.show', $object) }}">
                <svg class="icon icon-list"><use xlink:href="{{ $sprite }}#list"></use></svg><span>Список</span></a>
            <a class="toggle-block__btn btn btn-outline-light {{ $active === 'estimate' ? 'active' : '' }}" href="{{ route('estimate', $object) }}">
                <svg class="icon icon-file-list"><use xlink:href="{{ $sprite }}#file-list"></use></svg><span>Смета</span></a>
            @foreach (['tasks' => 'Задачи', 'gantt' => 'График работ', 'pen' => 'Заметки'] as $ico => $label)
                <span class="toggle-block__btn btn btn-outline-light crm-tab-soon" title="Следующий этап">
                    <svg class="icon icon-{{ $ico }}"><use xlink:href="{{ $sprite }}#{{ $ico }}"></use></svg><span>{{ $label }}</span></span>
            @endforeach
        </div>
    </div>
</div>
