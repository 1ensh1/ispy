#!/bin/bash

# Create storage directories
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

echo "=== Starting iSpy ==="
echo "PORT=$PORT"
echo "DB_CONNECTION=$DB_CONNECTION"
echo "DB_HOST=$DB_HOST"
echo "APP_KEY set: $([ -n "$APP_KEY" ] && echo 'YES' || echo 'NO')"

# Clear any stale config cache
php artisan config:clear 2>&1 || true

# Try migration
echo "=== Running migrations ==="
php artisan migrate --force 2>&1 || echo "Migration failed - continuing anyway"

# Start server
echo "=== Starting server on port ${PORT:-8080} ==="
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
