#!/bin/bash
# Выкладка на crm.ad-heads.ru (Beget): код из последнего коммита → ~/crm.ad-heads.ru/app, миграции, кэши.
# vendor собирается локально (Composer на Beget слишком старый для Laravel 13): ./deploy.sh --vendor
# .env и база на сервере не трогаются. Бэкап базы перед миграциями — в ~/crm.ad-heads.ru/backups/.
set -euo pipefail
cd "$(dirname "$0")"

HOST=pashtyt4@pashtyt4.beget.tech
KEY=~/.ssh/kazan_akb_ed25519
APP='~/crm.ad-heads.ru/app'
PHP=/usr/local/bin/php8.4
SSH=(ssh -i "$KEY" -o IdentitiesOnly=yes -o ControlMaster=auto -o ControlPath=/tmp/lps-crm-%r@%h -o ControlPersist=5m "$HOST")

[ -z "$(git status --porcelain)" ] || { echo "Есть незакоммиченные изменения — сначала коммит"; exit 1; }

echo "→ код $(git rev-parse --short HEAD)"
git archive --format=tar.gz HEAD | "${SSH[@]}" "cd $APP && tar -xzf -"

if [ "${1:-}" = "--vendor" ]; then
    echo "→ vendor"
    BUILD=$(mktemp -d)
    git archive HEAD | tar -x -C "$BUILD"
    (cd "$BUILD" && composer install --no-dev --optimize-autoloader --no-progress --no-scripts -q)
    COPYFILE_DISABLE=1 tar --no-xattrs -czf - -C "$BUILD" vendor | "${SSH[@]}" "cd $APP && rm -rf vendor && tar -xzf - 2>/dev/null"
    rm -rf "$BUILD"
fi

echo "→ бэкап базы, миграции, кэши"
"${SSH[@]}" "cd $APP && mkdir -p ../backups && set -a && . ./.env && set +a \
  && MYSQL_PWD=\"\$DB_PASSWORD\" mysqldump -h\"\$DB_HOST\" -u\"\$DB_USERNAME\" \"\$DB_DATABASE\" | gzip > ../backups/db-\$(date +%F_%H%M).sql.gz \
  && ls -1t ../backups/db-*.sql.gz | tail -n +31 | xargs -r rm -- \
  && $PHP artisan migrate --force && $PHP artisan optimize:clear >/dev/null && $PHP artisan optimize >/dev/null && echo готово"
