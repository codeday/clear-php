#!/bin/bash

# Migrate database and do some other things
php artisan migrate --force &
php artisan clear-compiled &

# Start nginx (daemonized)
nginx

# Keep php fpm running
php-fpm
