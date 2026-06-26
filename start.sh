#!/bin/bash
set -e

# Create storage directories
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Cache config at runtime (when env vars are available)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (non-blocking)
php artisan migrate --force || echo "Migration skipped or failed"

# Start the server
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
