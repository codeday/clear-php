#!/usr/bin/env sh

# Migrate database
php artisan route:cache
php artisan config:cache
php artisan queue:restart
php artisan migrate --force

# Start queue listeners
php artisan queue:listen --tries=3 --timeout=300 > /dev/null &

# Start nginx (daemonized)
nginx

# Keep php fpm running
php-fpm
