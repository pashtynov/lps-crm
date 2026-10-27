<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'CRM Лидерпрофстрой' }}</title>
    <link rel="shortcut icon" href="{{ asset('verstka/img/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('verstka/libs/swiper/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('verstka/css/main.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}?v=22">
    @livewireStyles
</head>
<body class="{{ $bodyClass ?? 'room-page' }}">
<div class="main-wrapper">
    @include('partials.aside', ['initials' => auth()->user() ? mb_strtoupper(mb_substr(auth()->user()->name, 0, 2)) : ''])
    <div class="main-center-wrap">
        <main>
            @if (session('status'))
                <div class="crm-flash">{{ session('status') }}</div>
            @endif
            {{ $slot ?? '' }}
            @yield('content')
        </main>
        <img class="page-bg" src="{{ asset('verstka/img/svg/page-bg.svg') }}" alt="" loading="lazy"/>
    </div>
</div>
@livewireScripts
<script src="{{ asset('js/crm.js') }}?v=1"></script>
</body>
</html>
