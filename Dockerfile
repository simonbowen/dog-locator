FROM serversideup/php:8.4-fpm-nginx

WORKDIR /var/www/html

COPY composer.* .
RUN composer install --no-dev

COPY . /var/www/html