FROM php:8.3-fpm-alpine

# Instalacja zależności systemowych (+ nginx, supervisor, envsubst do łączenia kontenerów)
RUN apk add --no-cache \
    git \
    unzip \
    icu-dev \
    postgresql-dev \
    nginx \
    supervisor \
    gettext

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

# Szablon konfiguracji Nginx (port podstawiany w runtime) i Supervisor
COPY docker/nginx/default.conf.template /etc/nginx/http.d/default.conf.template
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Skrypt startowy odpowiedzialny za migracje bazy danych
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Domyślny port lokalny — Railway nadpisze własną wartością $PORT
ENV PORT=8080
EXPOSE 8080

ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
