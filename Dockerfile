FROM srnd/docker-php:de08bebdbf02337861c68630004d9bbc74704a1e
WORKDIR /app

# Copy crontab
COPY ./docker/crontab /etc/cron.d/clear
RUN chmod 0644 /etc/cron.d/clear \
    && crontab /etc/cron.d/clear

# Install dependencies
COPY composer.* ./
RUN composer install --no-autoloader --no-scripts

# Copy code to the docker container
COPY . .
RUN composer dump-autoload

# Fix permissions for some directories
RUN mkdir -p /app/storage /app/storage/logs /app/storage/framework /app/boostrap \
    && chown -R www-data /app/storage \
    && chown -R www-data /app/bootstrap \
    && ln -sf /dev/fd/2 /app/storage/logs/laravel.log

# Run Clear
CMD /app/docker/docker-entrypoint.sh
