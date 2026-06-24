#!/bin/sh
set -e

# Podstawiamy $PORT w konfiguracji Nginx (lokalnie domyślnie 8080, na Railway nadpisane)
echo "Podstawiam PORT=${PORT} w konfiguracji Nginx..."
envsubst '$PORT' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

# Regenerujemy autoload (w tym autoload_runtime.php symfony/runtime) — zabezpieczenie
# przed problemami z cache warstw budowania na niektórych platformach (np. Railway)
echo "Generuję autoload (composer dump-autoload)..."
composer dump-autoload --optimize --no-interaction

# Wykonujemy migracje automatycznie przy starcie kontenera
php bin/console doctrine:migrations:migrate --no-interaction

echo "Uruchamiam supervisord (nginx + php-fpm)..."
exec "$@"