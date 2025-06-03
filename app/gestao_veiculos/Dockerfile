FROM localhost/php:8.2-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git unzip && pecl install redis \
    && docker-php-ext-enable gnupg redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer require \
    phpmailer/phpmailer \
    spomky-labs/otphp

COPY . /var/www/html/


EXPOSE 80
