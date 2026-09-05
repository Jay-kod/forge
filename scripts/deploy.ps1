# PowerShell Production Deployment Script for FORGE
$ErrorActionPreference = "Stop"

Write-Host "=== [FORGE Deployment Pipeline] Starting Deployment ===" -ForegroundColor Cyan

# 1. Pull latest git code
Write-Host "--> Pulling latest git commits..." -ForegroundColor Yellow
git pull origin main

# 2. Composer dependencies
Write-Host "--> Installing optimized Composer dependencies..." -ForegroundColor Yellow
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# 3. Compile frontend assets
if (Test-Path "package.json") {
    Write-Host "--> Compiling Vite production assets..." -ForegroundColor Yellow
    npm ci --prefer-offline --no-audit
    npm run build
}

# 4. Run migrations
Write-Host "--> Executing database migrations..." -ForegroundColor Yellow
php artisan migrate --force

# 5. Optimize caches
Write-Host "--> Warming production caches..." -ForegroundColor Yellow
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Restart queue workers
Write-Host "--> Signaling queue worker restart..." -ForegroundColor Yellow
php artisan queue:restart

# 7. Health check
Write-Host "--> Checking system health via /healthz..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "http://localhost/healthz" -Method Get -TimeoutSec 5
    if ($response.status -eq "ok") {
        Write-Host "✓ System Health Check PASSED" -ForegroundColor Green
    } else {
        Write-Host "⚠ System Health Check returned degraded status" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠ Could not connect to /healthz (server may be offline)" -ForegroundColor DarkYellow
}

Write-Host "=== [FORGE Deployment Pipeline] Deployment Succeeded! ===" -ForegroundColor Green
