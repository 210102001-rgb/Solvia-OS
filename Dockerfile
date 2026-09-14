# Solvia.Nova OS — PRODUCTION image.
# Secrets are NOT baked in (.dockerignore excludes .env) — pass them as
# container environment at runtime (see compose.yaml). OPcache has
# validate_timestamps=0, so code changes require a rebuild, never a volume mount.

# ── Stage 1: frontend assets (Vite + Tailwind) ─────────────────────────────
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm ci && npm run build

# ── Stage 2: PHP runtime (Apache + PHP 8.3) ────────────────────────────────
FROM php:8.3-apache-bookworm

ENV DEBIAN_FRONTEND=noninteractive \
    APACHE_DOCUMENT_ROOT=/var/www/html/public \
    COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=production \
    LOG_CHANNEL=stderr \
    QUEUE_CONNECTION=database

# System deps + PHP extensions Laravel 12 needs (mysql + sqlite drivers).
# Retry apt-get update: Debian mirrors occasionally flake on first hit.
RUN rm -rf /var/lib/apt/lists/* \
    && for i in 1 2 3; do apt-get update && break || { echo "apt-get update attempt $i failed, retrying..."; sleep 10; }; done \
    && apt-get install -y --no-install-recommends \
        git unzip curl ca-certificates \
        libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
        libzip-dev libicu-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql pdo_sqlite bcmath exif gd intl mbstring opcache pcntl zip \
    && a2enmod rewrite headers \
    && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\nServerTokens Prod\nServerSignature Off\n' > /etc/apache2/conf-available/novaos.conf \
    && a2enconf novaos \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer binary from official image (no extra PHP needed).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP deps first for better layer caching.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts

# Copy the rest of the app, then overlay freshly built frontend assets
# (public/build is excluded from the build context via .dockerignore).
COPY . .
COPY --from=frontend /app/public/build ./public/build

# Finish Laravel install steps that need full source, then drop dev-only files.
RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache \
    && rm -rf tests scratch \
    && php artisan storage:link || true

COPY docker/php.prod.ini /usr/local/etc/php/conf.d/novaos-prod.ini
COPY docker/entrypoint.sh /usr/local/bin/novaos-entrypoint.sh
RUN chmod +x /usr/local/bin/novaos-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["novaos-entrypoint.sh"]
CMD ["apache2-foreground"]
