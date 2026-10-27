<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
    <title>Вход — CRM Лидерпрофстрой</title>
    <link rel="stylesheet" href="{{ asset('verstka/css/main.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}?v=24">
</head>
<body class="crm-login">
<form method="post" action="{{ url('/login') }}" class="crm-card crm-login__card">
    @csrf
    <img src="{{ asset('verstka/img/svg/logo.svg') }}" alt="" class="mb-4">
    <h2>Вход</h2>
    <input class="form-control mb-3" type="email" name="email" value="{{ old('email') }}" placeholder="Почта" required autofocus>
    <input class="form-control mb-3" type="password" name="password" placeholder="Пароль" required>
    @error('email')<div class="crm-error mb-3">{{ $message }}</div>@enderror
    <button class="btn btn-accent w-100">Войти</button>
</form>
</body>
</html>
