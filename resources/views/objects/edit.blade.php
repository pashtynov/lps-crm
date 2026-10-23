@extends('layouts.form', ['title' => 'Объект — ' . $object->name])

@php($sprite = asset('verstka/img/svg/sprite.svg'))

@section('content')
    <form class="sCreateObject" method="post" action="{{ route('objects.update', $object) }}">
        @csrf @method('PUT')
        <div class="sCreateObject__body">
            <div class="sCreateObject__left-side">
                <h2>Редактирование объекта</h2>
                <div class="form-wrap">
                    <div class="form-wrap__main-row row">
                        <div class="col-12">
                            <div class="custom-input-wrap form-group">
                                <input class="custom-input-wrap__input form-control" name="name" value="{{ old('name', $object->name) }}" placeholder="Введите название" required>
                                <div class="custom-input-wrap__line"></div>
                            </div>
                            @error('name')<div class="crm-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6"><label class="crm-label">Адрес</label><input class="form-control" name="address" value="{{ old('address', $object->address) }}"></div>
                        <div class="col-md-6"><label class="crm-label">Заказчик</label><input class="form-control" name="customer" value="{{ old('customer', $object->customer) }}"></div>
                        <div class="col-12">
                            <div class="form-wrap__input-wrap form-group">
                                <textarea class="form-wrap__input form-control" name="note" placeholder="Описание объекта">{{ old('note', $object->note) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sCreateObject__right-side">
                <div class="sCreateObject__row row">
                    <div class="col-12">
                        <div class="sCreateObject__item">
                            <p>Статус</p>
                            <select class="form-select" name="status">
                                @foreach (\App\Models\CrmObject::STATUSES as $k => $label)
                                    <option value="{{ $k }}" @selected(old('status', $object->status) === $k)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sCreateObject__item">
                            <p>НДС в смете, %</p>
                            <input class="form-control" name="vat_percent" inputmode="decimal" value="{{ old('vat_percent', rtrim(rtrim(number_format((float) $object->vat_rate * 100, 2, '.', ''), '0'), '.')) }}">
                        </div>
                    </div>
                    <div class="col-12"><div class="sCreateObject__item"><p>Дата создания</p><span>{{ $object->created_at->format('d.m.Y, H:i') }}</span></div></div>
                    <div class="col-12"><div class="sCreateObject__item"><p>Последнее изменение</p><span>{{ $object->updated_at->format('d.m.Y, H:i') }}</span></div></div>
                    <div class="col-12">
                        <div class="sCreateObject__btn-row row">
                            <div class="col-auto">
                                <button class="sCreateObject__control-btn sCreateObject__control-btn--delete btn btn-light" title="Удалить объект"
                                        form="delete-object" onclick="return confirm('Удалить объект «{{ $object->name }}» со всеми помещениями и сметами?')">
                                    <svg class="icon icon-delete"><use xlink:href="{{ $sprite }}#delete"></use></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sCreateObject__footer">
            <div class="sCreateObject__footer-row">
                <button class="sCreateObject__btn btn btn-accent">Сохранить изменения</button>
                <a class="sCreateObject__btn btn btn-light" href="{{ route('objects.show', $object) }}">Отмена</a>
            </div>
        </div>
    </form>
    <form id="delete-object" method="post" action="{{ route('objects.destroy', $object) }}">@csrf @method('DELETE')</form>
    <a class="close-btn" href="{{ route('objects.show', $object) }}" title="Закрыть"><svg class="icon icon-close"><use xlink:href="{{ $sprite }}#close"></use></svg></a>
@endsection
