FROM php:8.2-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git unzip && pecl install redis \
    && docker-php-ext-enable redis

COPY . /var/www/html/

EXPOSE 80
