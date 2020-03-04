#!/bin/bash

# Migrate database
php artisan migrate --force

# Start queue listeners
sudo -u www-data php artisan queue:listen --tries=3 --timeout=300 > /dev/null &

# Start nginx (daemonized)
nginx

# Keep php fpm running
php-fpm
