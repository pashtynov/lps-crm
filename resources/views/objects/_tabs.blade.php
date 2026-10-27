{{-- Вкладки объекта — toggle-block из макета. Шахматка, задачи, график, бригады — концепт следующих этапов (/concept) --}}
@php($sprite = asset('verstka/img/svg/sprite.svg'))
<div class="sMainInfo__wrap">
    <div class="toggle-block">
        <div class="crm-tabs-row">
            <a class="toggle-block__btn btn btn-outline-light {{ $active === 'list' ? 'active' : '' }}" href="{{ route('objects.show', $object) }}">
                <svg class="icon icon-list"><use xlink:href="{{ $sprite }}#list"></use></svg><span>Список</span></a>
            <a class="toggle-block__btn btn btn-outline-light {{ $active === 'estimate' ? 'active' : '' }}" href="{{ route('estimate', $object) }}">
                <svg class="icon icon-file-list"><use xlink:href="{{ $sprite }}#file-list"></use></svg><span>Смета</span></a>
            @foreach (['chess' => ['tile', 'Шахматка'], 'today' => ['tasks', 'Задачи'], 'schedule' => ['gantt', 'График работ'], 'brigades' => ['workers', 'Бригады']] as $tab => [$ico, $label])
                <a class="toggle-block__btn btn btn-outline-light crm-tab-concept" href="{{ route('concept', $tab) }}" title="Концепт следующего этапа на примерных данных">
                    <svg class="icon icon-{{ $ico }}"><use xlink:href="{{ $sprite }}#{{ $ico }}"></use></svg><span>{{ $label }}</span><sup>концепт</sup></a>
            @endforeach
        </div>
    </div>
</div>
