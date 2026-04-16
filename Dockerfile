# ============================================
# Dockerfile — Clothing Store (PHP 8.1 MVC)
# Optimized for Render.com deployment
# ============================================

# ---------- Stage 1: Composer dependencies ----------
FROM composer:2 AS vendor

WORKDIR /app

# Copy only composer manifests first to maximize Docker layer caching
COPY composer.json ./
COPY composer.lock* ./

# Install production dependencies without running scripts (no app code yet)
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts \
        --ignore-platform-reqs \
 && composer clear-cache

# ---------- Stage 2: Runtime image ----------
FROM php:8.1-cli-alpine AS runtime

# Install required system libraries and PHP extensions
# - postgresql-dev / libpq : build & runtime for pdo_pgsql
# - oniguruma-dev          : required by mbstring
# - icu-dev                : optional locale support
RUN set -eux; \
    apk add --no-cache \
        bash \
        libpq \
        libzip \
        oniguruma \
        icu-libs \
        tzdata; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        postgresql-dev \
        libzip-dev \
        oniguruma-dev \
        icu-dev; \
    docker-php-ext-configure pdo_pgsql --with-pdo-pgsql=/usr/include; \
    docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        opcache; \
    apk del .build-deps; \
    rm -rf /var/cache/apk/* /tmp/*

# Production-tuned PHP ini settings
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=0'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.revalidate_freq=0'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'expose_php=Off'; \
        echo 'display_errors=Off'; \
        echo 'log_errors=On'; \
        echo 'error_log=/dev/stderr'; \
        echo 'date.timezone=Asia/Riyadh'; \
        echo 'memory_limit=256M'; \
        echo 'upload_max_filesize=10M'; \
        echo 'post_max_size=12M'; \
    } > /usr/local/etc/php/conf.d/zz-production.ini

# Application working directory
WORKDIR /var/www/html

# Copy vendor dependencies from builder stage
COPY --from=vendor /app/vendor ./vendor

# Copy application source code
COPY . .

# Ensure uploads directory exists and is writable
RUN mkdir -p public/assets/images/uploads \
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html \
 && chmod -R 775 public/assets/images/uploads

# Security: drop privileges to non-root user
USER www-data

# Render injects $PORT at runtime (default to 8080 for local use)
ENV PORT=8080 \
    APP_ENV=production

EXPOSE 8080

# Healthcheck hitting the app's /api/health endpoint
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD php -r "exit(@file_get_contents('http://127.0.0.1:'.getenv('PORT').'/api/health') ? 0 : 1);"

# Start PHP's built-in server, routing every request through public/index.php
# NOTE: we exec through /bin/sh so that ${PORT} is expanded at container start
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public public/index.php"]
