# syntax=docker/dockerfile:1

# ──────────────────────────────────────────────────────────────────────────────
# Dependencies
#
# Built in its own stage so the application image never carries Composer or the
# build toolchain. The manifest is copied on its own first, so the dependency
# layer is only rebuilt when composer.lock actually changes rather than on every
# edit to the source.
# ──────────────────────────────────────────────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --no-progress

COPY . .

RUN composer dump-autoload --no-dev --optimize


# ──────────────────────────────────────────────────────────────────────────────
# Application
# ──────────────────────────────────────────────────────────────────────────────
FROM php:8.4-apache AS app

# The extensions need development headers to compile against, but only the
# shared libraries to run. Everything is installed, the extensions are built,
# and then the headers are dropped again — the libraries the compiled .so files
# actually link against are marked manual first so they survive the purge.
RUN set -eux; \
    savedAptMark="$(apt-mark showmanual)"; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev; \
    docker-php-ext-configure gd --with-jpeg --with-freetype; \
    docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gd \
        intl \
        pdo_mysql \
        zip \
        opcache; \
    apt-mark auto '.*' > /dev/null; \
    apt-mark manual $savedAptMark > /dev/null; \
    ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
        | awk '/=>/ { so = $(NF-1); if (index(so, "/usr/local/") == 1) next; gsub("^/(usr/)?", "", so); printf "*/%s\n", so }' \
        | sort -u \
        | xargs -r dpkg-query --search 2>/dev/null \
        | cut -d: -f1 \
        | sort -u \
        | xargs -r apt-mark manual > /dev/null; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
    rm -rf /var/lib/apt/lists/*

# Laravel serves from public/, so nothing above it is ever reachable over HTTP.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN a2enmod rewrite \
    && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf

COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .

# The uid the worker processes run as.
#
# A release image keeps Debian's own www-data. Development overrides it with the
# host user's uid, because the source is bind mounted there: the files on disk
# belong to the host user, and a worker running as anyone else cannot write to
# storage or the view cache.
ARG APP_UID=33

RUN if [ "$APP_UID" != "33" ]; then usermod --uid "$APP_UID" www-data; fi

# NOTE: Apache's master process stays root so it can bind port 80; its workers
# drop to www-data, which is what actually touches these paths. Running the
# whole thing unprivileged would mean an unprivileged port and a proxy in front.
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

HEALTHCHECK --interval=15s --timeout=5s --start-period=20s --retries=5 \
    CMD curl -fsS http://localhost/up || exit 1

CMD ["apache2-foreground"]
