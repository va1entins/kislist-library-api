#!/bin/sh
set -e

# Podstawiamy $PORT w konfiguracji Nginx (lokalnie domyślnie 8080, na Railway nadpisane)
echo "Podstawiam PORT=${PORT} w konfiguracji Nginx..."
envsubst '$PORT' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

# Regenerujemy autoload (w tym autoload_runtime.php symfony/runtime) — zabezpieczenie
# przed problemami z cache warstw budowania na niektórych platformach (np. Railway)
echo "Generuję autoload (composer dump-autoload)..."
composer dump-autoload --optimize --no-interaction

# Czyścimy cache Symfony — zabezpieczenie przed nieaktualnym cache z poprzedniego builda.
# Skrypt działa jako root, więc po wygenerowaniu cache trzeba oddać prawa www-data
# (PHP-FPM worker działa jako www-data i inaczej dostaje 500 przy próbie odczytu/zapisu).
echo "Czyszczę cache Symfony..."
php bin/console cache:clear --no-warmup
chown -R www-data:www-data var/cache var/log

# Wykonujemy migracje automatycznie przy starcie kontenera
php bin/console doctrine:migrations:migrate --no-interaction

echo "Uruchamiam supervisord (nginx + php-fpm)..."
exec "$@"