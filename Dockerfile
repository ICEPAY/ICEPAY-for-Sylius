FROM php:8.2-apache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN apt-get update && apt-get -y install zip git libsodium-dev zlib1g-dev libzip-dev libpng-dev libicu-dev

RUN docker-php-ext-install exif sodium gd intl zip

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf



RUN COMPOSER_MEMORY_LIMIT=-1 composer create-project sylius/sylius-standard /var/www/html/
