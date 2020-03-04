FROM srnd/docker-php

# Install dependencies
COPY composer* ./
RUN composer install --no-autoloader --no-scripts

# Install application
COPY . .
RUN composer dump-autoload \
    && php artisan clear-compiled \
    && php artisan route:cache \
    && php artisan config:cache
