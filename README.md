# CRM Лидерпрофстрой

Замер помещения → смета по нормам и ценам клиента → выгрузка в Excel и PDF. Этап 1 плана
`clients/lps/docs/crm-plan.md` (репозиторий adheads); формулы и нормы — `clients/lps/docs/crm-formuly.md`.

Стек: Laravel + Livewire, вёрстка MegaFron (`public/verstka/`, наша), Excel — PhpSpreadsheet, PDF — mPDF.

## Где что

| Что | Где |
|---|---|
| Расчёт площадей помещения | `app/Calc/Geometry.php` |
| Расчёт сметы (округление, НДС) | `app/Calc/Estimator.php`, десятичная арифметика — `app/Calc/Dec.php` |
| Справочник видов работ, материалов, норм, цен | `database/data/catalog.json` |
| Смета по объекту из помещений и готовых объёмов | `app/Calc/ObjectEstimate.php` |
| Эталон: смета Бетанкура 20.05.2024 | `tests/fixtures/betancura-2024-05-20.json`, тест `tests/Unit/CalcTest.php` |
| Экраны замера и сметы | `app/Livewire/`, `resources/views/livewire/` |
| Выгрузка | `app/Export/EstimateXlsx.php`, `resources/views/exports/estimate.blade.php` |

## Запуск локально (Laravel Herd)

    composer install
    cp .env.example .env && php artisan key:generate
    php artisan migrate --seed
    php artisan test
    php artisan serve

Первый пользователь создаётся сидером из `CRM_ADMIN_EMAIL` / `CRM_ADMIN_PASSWORD` в `.env`.
Пароли в репозиторий не кладём.

**Главный тест** — `php artisan test --filter=Бетанкура`: справочник должен давать 32 720 239,11 ₽.
Если поменяли нормы или цены и тест упал — это ожидаемо только при осознанной смене справочника.

## Стенд crm.ad-heads.ru (Beget)

| Что | Где |
|---|---|
| Код | `~/crm.ad-heads.ru/app/`, `public_html` → ссылка на `app/public` |
| Настройки и пароли | `~/crm.ad-heads.ru/app/.env` — только на сервере, заполняет Дмитрий |
| Бэкапы базы | `~/crm.ad-heads.ru/backups/` — делает `deploy.sh` перед миграциями, хранятся 30 последних |
| Старая статичная вёрстка | `~/crm.ad-heads.ru/verstka-static/` |
| PHP | `/usr/local/bin/php8.4` (по умолчанию в SSH — 5.6) |

Выкладка — `./deploy.sh` (код последнего коммита, бэкап базы, миграции, кэши),
`./deploy.sh --vendor` — если менялся `composer.lock`: Composer на Beget слишком старый, vendor собирается локально.

MySQL на сервере строже SQLite в тестах: `DISTINCT` вместе с `orderBy` по невыбранной колонке падает —
у связей с сортировкой перед `distinct()` нужен `reorder()`.
