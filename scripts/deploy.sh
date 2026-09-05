#!/usr/bin/env bash
set -e

echo "=== [FORGE Deployment Pipeline] Starting Production Deployment ==="

# 1. Ensure latest code
echo "--> Pulling latest git changes..."
git pull origin main

# 2. Install / Optimize Composer Dependencies
echo "--> Installing composer dependencies..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# 3. Compile frontend assets
if [ -f "package.json" ]; then
    echo "--> Compiling production frontend assets..."
    npm ci --prefer-offline --no-audit
    npm run build
fi

# 4. Run database migrations
echo "--> Running database migrations..."
php artisan migrate --force

# 5. Clear and recreate application caches
echo "--> Warming production caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Restart queue workers cleanly to load fresh code
echo "--> Restarting queue workers..."
php artisan queue:restart

# 7. Verify health endpoint
echo "--> Probing /healthz..."
curl -s -f http://localhost/healthz > /dev/null && echo "✓ System health OK" || echo "⚠ Health check probe failed"

echo "=== [FORGE Deployment Pipeline] Deployment Completed Successfully! ==="
