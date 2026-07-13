FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    git curl zip unzip oniguruma-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev \
    icu-dev mysql-client bash \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl gd intl bcmath

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www

# ponytail: run as root inside container, add www-data user if hardening needed
EXPOSE 9000
CMD ["php-fpm"]
