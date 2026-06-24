FROM php:8.3-fpm-alpine

# Instalacja zależności systemowych
RUN apk add --no-cache \
    git \
    unzip \
    icu-dev \
    postgresql-dev

# Instalacja rozszerzeń PHP wymaganych przez Symfony + Doctrine (PostgreSQL)
RUN docker-php-ext-install \
    pdo_pgsql \
    intl \
    opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-interaction --optimize-autoloader --no-scripts

RUN chown -R www-data:www-data var
# Skrypt startowy odpowiedzialny za migracje bazy danych
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.shin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
