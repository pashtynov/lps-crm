@extends('layouts.app', ['title' => 'Объекты — CRM Лидерпрофстрой'])

@section('content')
    @include('partials.page-head', ['heading' => 'Объекты', 'crumbs' => []])

    <div class="section">
        <div class="container-fluid">
            <div class="main-table main-table--2">
                <table>
                    <thead>
                    <tr><th>Объект</th><th>Заказчик</th><th>Помещений</th><th>Статус</th><th>Изменён</th></tr>
                    </thead>
                    <tbody>
                    @forelse ($objects as $o)
                        <tr>
                            <td><div class="main-table__title"><a href="{{ route('objects.show', $o) }}">{{ $o->name }}</a></div>
                                @if ($o->address)<div class="text-secondary small">{{ $o->address }}</div>@endif</td>
                            <td>{{ $o->customer }}</td>
                            <td>{{ $o->rooms_count }}</td>
                            <td><div class="badge badge-primary">{{ $o->statusLabel() }}</div></td>
                            <td>{{ $o->updated_at->format('d.m.Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Объектов пока нет.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <form class="crm-card mt-4" method="post" action="{{ route('objects.store') }}">
                @csrf
                <h3>Новый объект</h3>
                <div class="row g-3">
                    <div class="col-md-4"><input class="form-control" name="name" placeholder="Название, например ЖК Видный" required></div>
                    <div class="col-md-4"><input class="form-control" name="address" placeholder="Адрес"></div>
                    <div class="col-md-3"><input class="form-control" name="customer" placeholder="Заказчик"></div>
                    <div class="col-md-1"><button class="btn btn-accent w-100">Создать</button></div>
                </div>
            </form>
        </div>
    </div>
@endsection
