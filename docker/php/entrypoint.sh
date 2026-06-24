#!/bin/sh
set -e

# Czekamy, aż baza danych będzie gotowa (depends_on z healthcheck już to zapewnia,
# ale dajemy dodatkowy margines bezpieczeństwa)
echo "Czekam na bazę danych..."

# Wykonujemy migracje automatycznie przy starcie kontenera
php bin/console doctrine:migrations:migrate --no-interaction

# Uruchamiamy główny proces (php-fpm)
exec "$@"
