FROM php:8.2-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git gnupg2 unzip && pecl install gnupg redis \
    && docker-php-ext-enable gnupg redis

COPY . /var/www/html/

EXPOSE 80