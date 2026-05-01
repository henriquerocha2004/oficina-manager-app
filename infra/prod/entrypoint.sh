#!/usr/bin/env sh
set -eu

if [ "${CONTAINER_ROLE:-app}" = "app" ]; then
    if [ "${WARMUP_CACHE:-true}" = "true" ]; then
        php artisan config:cache
        php artisan route:cache
    fi

    if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
        php artisan migrate --force
        php artisan app:migrate-tenants
    fi
fi

exec "$@"
