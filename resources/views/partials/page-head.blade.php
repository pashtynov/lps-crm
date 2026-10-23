{{-- Шапка страницы из вёрстки: крошки, заголовок, действия справа --}}
<div class="page-head">
    <div class="container-fluid">
        <div class="page-head__row row align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('objects.index') }}"><span>Объекты</span></a></li>
                        @foreach ($crumbs ?? [] as $label => $url)
                            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}"><a href="{{ $url }}"><span>{{ $label }}</span></a></li>
                        @endforeach
                    </ol>
                </nav>
            </div>
            <div class="page-head__col page-head__col--title col-lg order-md-4">
                <h1>{{ $heading }}
                    @isset($badge)<div class="badge badge-primary">{{ $badge }}</div>@endisset
                </h1>
            </div>
            <div class="col-12 d-none d-md-block m-0 p-0 order-md-3"></div>
            @isset($actions)
                <div class="page-head__col col-md-auto order-md-2">{!! $actions !!}</div>
            @endisset
        </div>
    </div>
</div>
