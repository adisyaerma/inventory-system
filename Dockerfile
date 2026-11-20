# Tahap 1: install dependency PHP
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev --ignore-platform-reqs

# Tahap 2: image final (PHP-FPM + Nginx sudah satu paket)
FROM serversideup/php:8.3-fpm-nginx
USER root
COPY --chown=www-data:www-data --from=vendor /app /var/www/html
USER www-data