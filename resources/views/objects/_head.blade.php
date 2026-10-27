{{-- Компактная шапка объекта для вкладок, где важнее содержимое (смета): крошки, название, статус --}}
@php([, , $icon] = $object->statusStyle())
@php($color = $object->statusStyle()[0])
<div class="page-head">
    <div class="container-fluid">
        <div class="page-head__row row align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('objects.index') }}"><span>Объекты</span></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('objects.show', $object) }}"><span>{{ $object->name }}</span></a></li>
                        <li class="breadcrumb-item active"><a href="#"><span>{{ $crumb }}</span></a></li>
                    </ol>
                </nav>
            </div>
            <div class="page-head__col col-md-auto order-md-2">
                <a class="page-head__edit" href="{{ route('objects.edit', $object) }}">Редактировать
                    <svg class="icon icon-edit"><use xlink:href="{{ asset('verstka/img/svg/sprite.svg') }}#edit"></use></svg>
                </a>
            </div>
            <div class="page-head__col page-head__col--title col-lg order-md-4">
                <h1>{{ $object->name }}
                    <span class="crm-status" style="--c: {{ $color }}"><img src="{{ asset('verstka/img/svg/' . $icon) }}" alt="">{{ $object->statusLabel() }}</span>
                </h1>
                @if ($object->address || $object->customer)
                    <div class="crm-muted">{{ collect([$object->address, $object->customer])->filter()->implode(' · ') }}</div>
                @endif
            </div>
            <div class="col-12 d-none d-md-block m-0 p-0 order-md-3"></div>
        </div>
    </div>
</div>
