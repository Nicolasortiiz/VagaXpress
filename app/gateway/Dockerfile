FROM php:8.2-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git unzip 

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json composer.lock ./


RUN composer update --no-interaction --prefer-dist --optimize-autoloader

COPY . /var/www/html/

RUN a2enmod rewrite

EXPOSE 80