#!/bin/sh
set -e

# Podstawiamy $PORT w konfiguracji Nginx (lokalnie domyślnie 8080, na Railway nadpisane)
echo "Podstawiam PORT=${PORT} w konfiguracji Nginx..."
envsubst '$PORT' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

# Wykonujemy migracje automatycznie przy starcie kontenera
php bin/console doctrine:migrations:migrate --no-interaction

echo "Uruchamiam supervisord (nginx + php-fpm)..."
exec "$@"
