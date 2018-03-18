#!/bin/bash

# HACK copy the config env variable to `config/local.json`
echo $CONFIG > ./config/local.json

# Migrate database and do some other things
php artisan migrate --force &
php artisan clear-compiled &

# Start nginx (daemonized)
nginx

# Keep php fpm running
php-fpm