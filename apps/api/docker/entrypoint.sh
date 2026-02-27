#!/bin/sh
set -e

echo "==> Waiting for database..."
# Tunggu PostgreSQL siap
until php -r "
    \$conn = pg_connect('host=' . getenv('DB_HOST') . 
        ' port=' . getenv('DB_PORT') . 
        ' dbname=' . getenv('DB_DATABASE') . 
        ' user=' . getenv('DB_USERNAME') . 
        ' password=' . getenv('DB_PASSWORD'));
    if (!\$conn) exit(1);
    pg_close(\$conn);
    exit(0);
" 2>/dev/null; do
    echo "Database not ready, retrying in 2s..."
    sleep 2
done
echo "==> Database ready."

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> Starting services..."
exec "$@"