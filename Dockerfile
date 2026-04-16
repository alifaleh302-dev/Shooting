# ============================================
# Dockerfile — Clothing Store (PHP 8.1 MVC)
# Optimized for Render.com deployment
# ============================================
# This project has NO external Composer dependencies (only PHP core extensions),
# so we only need to generate the PSR-4 autoloader — no vendor packages to install.

FROM php:8.1-cli-alpine

# ---------- System & PHP extensions ----------
# Install runtime libs + build deps in one layer, compile extensions, then purge build deps.
RUN set -eux; \
    apk add --no-cache \
        bash \
        libpq \
        oniguruma \
        icu-libs \
        tzdata; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        postgresql-dev \
        oniguruma-dev \
        icu-dev; \
    docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        opcache; \
    apk del .build-deps; \
    rm -rf /var/cache/apk/* /tmp/* /var/tmp/*

# ---------- Install Composer (for autoloader generation only) ----------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------- Production PHP configuration ----------
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

# ---------- Application ----------
WORKDIR /var/www/html

# Copy composer manifest first for better layer caching
COPY composer.json ./
COPY composer.lock* ./

# Generate optimized PSR-4 autoloader (no external deps to install)
RUN composer dump-autoload --optimize --no-dev --no-interaction 2>/dev/null || \
    composer install --no-dev --no-interaction --no-progress --optimize-autoloader --no-scripts

# Copy application source code
COPY . .

# Regenerate autoloader now that all source files are in place
RUN composer dump-autoload --optimize --no-dev --no-interaction

# Ensure uploads directory exists and has correct permissions
RUN mkdir -p public/assets/images/uploads \
 && chmod -R 755 /var/www/html \
 && chmod -R 775 public/assets/images/uploads

# ---------- Runtime ----------
# Render injects $PORT at runtime (we default to 8080 for local use)
ENV PORT=8080 \
    APP_ENV=production

EXPOSE 8080

# Healthcheck hitting the app's /api/health endpoint
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD php -r "exit(@file_get_contents('http://127.0.0.1:'.getenv('PORT').'/api/health') ? 0 : 1);"

# Start PHP's built-in server, routing every request through public/index.php.
# Executed via /bin/sh so ${PORT} is expanded at container start time.
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public public/index.php"]
