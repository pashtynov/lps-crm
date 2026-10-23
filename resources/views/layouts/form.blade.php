<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'CRM Лидерпрофстрой' }}</title>
    <link rel="shortcut icon" href="{{ asset('verstka/img/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('verstka/css/main.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}?v=9">
    @livewireStyles
</head>
{{-- Окно редактирования из макета (02-edit-room, 02-edit-object-d): без бокового меню, крестик — назад --}}
<body class="createObject-page">
<main>
    {{ $slot ?? '' }}
    @yield('content')
</main>
@livewireScripts
</body>
</html>
