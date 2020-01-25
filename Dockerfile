FROM php:7-fpm-alpine
WORKDIR /tmp

# Install php exts and their dependencies
RUN apk upgrade --update
RUN apk add curl autoconf

# Install GD
RUN apk add freetype-dev libjpeg-turbo-dev libpng-dev freetype libjpeg libpng
RUN docker-php-ext-install gd
RUN apk del freetype-dev libjpeg-turbo-dev libpng-dev

# Install MBString
RUN apk add oniguruma-dev oniguruma
RUN docker-php-ext-install mbstring
RUN apk del oniguruma-dev

# Install XML
RUN apk add libxml2-dev libxml2
RUN docker-php-ext-install xml
RUN apk del libxml2-dev

# Install MySQL
RUN docker-php-ext-install mysqli pdo_mysql

# Install ZIP
RUN apk add libzip-dev libzip
RUN docker-php-ext-install zip
RUN apk del libzip-dev

# Install PECL packages
ENV MAGICK_HOME=/usr
RUN apk add gcc g++ libmcrypt-dev dpkg-dev imagemagick-dev libc-dev dpkg make yaml-dev yaml libmcrypt file imagemagick
RUN pecl install imagick-beta redis yaml mcrypt
RUN docker-php-ext-enable imagick redis yaml mcrypt
RUN apk del gcc g++ libmcrypt-dev dpkg-dev imagemagick-dev libc-dev dpkg make yaml-dev

# Install Composer
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
RUN composer global require hirak/prestissimo

# Install Composer dependencies
RUN apk add git
WORKDIR /app
COPY composer* ./
RUN composer install --no-autoloader --no-scripts

# Copy code to the docker container and run it
COPY . .
RUN composer dump-autoload

# Make some dirs for mount points
RUN mkdir -p /app/storage /app/storage/logs /app/storage/framework /app/boostrap
RUN ln -sf /dev/fd/2 /app/storage/logs/laravel.log

# Fix permissions for some directories
RUN chown -R www-data /app/storage
RUN chown -R www-data /app/bootstrap

# Install Nginx
RUN apk add nginx
COPY ./docker/nginx-site /etc/nginx/conf.d/default.conf
COPY ./docker/php-fpm.conf /usr/local/etc/php-fpm.d/enable-logging.conf
RUN mkdir /run/nginx

# Run it!
EXPOSE 80
CMD [ "sh", "./docker-entrypoint.sh" ]
