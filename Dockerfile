FROM composer:2 AS dependencies

WORKDIR /app
COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

FROM php:8.4-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite \
    && printf 'variables_order = "EGPCS"\n' > /usr/local/etc/php/conf.d/zz-app-env.ini

WORKDIR /var/www/html
COPY . /var/www/html
COPY --from=dependencies /app/vendor /var/www/html/vendor

EXPOSE 80
