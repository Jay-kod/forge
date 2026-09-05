#!/bin/sh
set -e

# Clear any stale bootstrap package caches
rm -f /var/www/html/bootstrap/cache/packages.php /var/www/html/bootstrap/cache/services.php

# Ensure required Laravel storage and cache directories exist
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Ensure correct permissions for the forge user
chown -R forge:forge /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# If the command starts with php-fpm, run php-fpm directly as root (workers run as forge)
if [ "$1" = "php-fpm" ]; then
    exec php-fpm
fi

# If running artisan or another command, execute as forge user
if [ "$(id -u)" = "0" ]; then
    exec su-exec forge "$@"
else
    exec "$@"
fi
