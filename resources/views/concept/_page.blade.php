{{-- Шапка страницы концепта: крошки, заголовок, статус, пометка «примерные данные» --}}
<div class="page-head">
    <div class="container-fluid">
        <div class="page-head__row row align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb"><ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('objects.index') }}"><span>Объекты</span></a></li>
                    @foreach ($crumbs as $label => $url)
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}"><a href="{{ $url }}"><span>{{ $label }}</span></a></li>
                    @endforeach
                </ol></nav>
            </div>
            <div class="page-head__col col-md-auto order-md-2"><span class="crm-concept-badge" title="Изменения сохраняются только в этом браузере — в базу CRM не пишутся">концепт · примерные данные</span></div>
            <div class="page-head__col page-head__col--title col-lg order-md-4">
                <h1>{{ $title }} @isset($status)<span class="crm-status" style="--c: {{ $statusColor ?? '#467CF4' }}">{{ $status }}</span>@endisset</h1>
                @isset($subtitle)<div class="crm-muted">{{ $subtitle }}</div>@endisset
            </div>
            <div class="col-12 d-none d-md-block m-0 p-0 order-md-3"></div>
        </div>
    </div>
</div>
