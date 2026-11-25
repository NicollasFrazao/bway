#!/bin/sh
# Prepares a fresh clone on the first `docker compose up` (dependencies, .env,
# app key, schema, demo data), then hands over to the command of the service.
set -eu

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --no-progress
fi

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --no-interaction
fi

# The seeders do nothing once store 1 exists, so this is safe on every start.
php artisan migrate --force --seed --no-interaction

exec "$@"
