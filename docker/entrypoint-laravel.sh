#!/bin/sh
set -e

cd /var/www

# Crear .env si no existe
if [ ! -f .env ]; then
    echo "Creando archivo .env..."
    cp .env.example .env
fi

# Instalar dependencias de Composer (crea carpeta vendor dentro del contenedor)
if [ ! -f vendor/autoload.php ]; then
    echo "Instalando dependencias de Composer (vendor)..."
    composer install --no-interaction --optimize-autoloader
fi

# Generar APP_KEY solo si falta o está vacía
if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
    echo "Generando APP_KEY..."
    php artisan key:generate
fi

echo "Iniciando servidor de Laravel..."
exec php artisan serve --host=0.0.0.0 --port=8000
