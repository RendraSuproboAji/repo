# Prefix registry untuk base image — override bila memakai mirror,
# mis. --build-arg BASE_REGISTRY=mirror.gcr.io/library/
ARG BASE_REGISTRY=docker.io/library/

# ---------- stage 1: dependensi composer ----------
FROM ${BASE_REGISTRY}composer:2 AS composer-deps

# CA tambahan opsional (proxy korporat/TLS interception): taruh file .crt
# di docker/certs/ — bila kosong, langkah ini tidak berefek apa pun
COPY docker/certs/ /usr/local/share/ca-certificates/
RUN update-ca-certificates 2>/dev/null || true

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev --no-interaction --no-progress --no-scripts \
    --prefer-dist --optimize-autoloader

# ---------- stage 2: node (untuk fitur konversi splat-transform) ----------
FROM ${BASE_REGISTRY}node:22-bookworm-slim AS node-src

# ---------- stage 3: runtime ----------
FROM ${BASE_REGISTRY}php:8.4-apache

# CA tambahan opsional (lihat catatan di stage composer); Node tidak membaca
# trust store sistem, jadi arahkan lewat NODE_EXTRA_CA_CERTS (aman walau
# tidak ada CA tambahan — file ini bundle CA standar Debian)
COPY docker/certs/ /usr/local/share/ca-certificates/
RUN update-ca-certificates 2>/dev/null || true
ENV NODE_EXTRA_CA_CERTS=/etc/ssl/certs/ca-certificates.crt

# ekstensi PHP: sqlite sudah built-in; pdo_mysql/pdo_pgsql disediakan
# agar tinggal ganti .env bila ingin memakai MySQL/PostgreSQL
RUN sed -i 's|http://deb.debian.org|https://deb.debian.org|g' \
        /etc/apt/sources.list.d/debian.sources 2>/dev/null || true \
    && apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev unzip curl \
    && docker-php-ext-install pdo_mysql pdo_pgsql \
    && apt-get purge -y --auto-remove libpq-dev \
    && apt-get install -y --no-install-recommends libpq5 curl \
    && rm -rf /var/lib/apt/lists/*

# Node.js + splat-transform untuk fitur konversi lokal (halaman /convert)
COPY --from=node-src /usr/local/bin/node /usr/local/bin/node
COPY --from=node-src /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s ../lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
    && npm install -g @playcanvas/splat-transform \
    && npm cache clean --force

# Apache: docroot ke public/ + mod_rewrite untuk routing Laravel
RUN a2enmod rewrite \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
        > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# batas unggahan file splat yang besar
RUN { \
        echo 'upload_max_filesize = 512M'; \
        echo 'post_max_size = 520M'; \
        echo 'memory_limit = 512M'; \
        echo 'max_execution_time = 600'; \
    } > /usr/local/etc/php/conf.d/splat-gallery.ini \
    && mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

WORKDIR /var/www/html

COPY . .
COPY --from=composer-deps /app/vendor ./vendor

RUN php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
