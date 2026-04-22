FROM php:8.2-cli

WORKDIR /app

COPY . .

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

# Ensure the SQLite database file exists and directories are writable
RUN mkdir -p database \
    && touch database/database.sqlite \
    && chmod -R 775 database storage bootstrap/cache \
    && chown -R www-data:www-data database storage bootstrap/cache

# Make artisan executable
RUN chmod +x artisan

EXPOSE 10000

CMD php artisan config:clear \
    && php artisan migrate --force \
    && php artisan serve --host=0.0.0.0 --port=10000