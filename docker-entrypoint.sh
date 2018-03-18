#!/bin/bash

# HACK copy the config env variable to `config/local.json`
echo $CONFIG > ./config/local.json

# Migrate database
php artisan migrate --force &

# Start nginx (daemonized)
nginx

# Keep php fpm running
php-fpm