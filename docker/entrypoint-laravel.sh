#!/bin/sh
set -e
cd /var/www
if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --optimize-autoloader
fi

# Generar APP_KEY si no existe en el .env
if [ -f .env ] && ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate
fi
exec php artisan serve --host=0.0.0.0 --port=8000
