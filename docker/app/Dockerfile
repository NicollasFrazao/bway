# Development image: FrankenPHP (Caddy + PHP 8.4 in one process) serving public/,
# with Composer and the extensions the app and the quality gate need.
FROM dunglas/frankenphp:1-php8.4-bookworm

# The repository is bind-mounted; running as the host user keeps the files it
# writes (vendor/, caches, coverage) owned by that user instead of root.
ARG UID=1000
ARG GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions bcmath gd intl opcache pcntl pcov pdo_mysql zip \
    && groupadd --gid "${GID}" app \
    && useradd --uid "${UID}" --gid app --create-home app \
    && chown -R app:app /config/caddy /data/caddy /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/app/php.ini /usr/local/etc/php/conf.d/zz-app.ini

# Port 8000 instead of 80 so the server can run without root.
ENV SERVER_NAME=:8000

USER app
WORKDIR /app
EXPOSE 8000
