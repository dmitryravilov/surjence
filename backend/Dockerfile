FROM php:8.2-cli-alpine AS base

RUN apk add --no-cache \
    git curl libpng-dev libzip-dev zip unzip postgresql-dev \
    oniguruma-dev bash autoconf g++ make

RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath zip gd
RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock* ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
