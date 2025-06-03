FROM localhost/php:8.2-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git unzip 

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer require \
    psr/http-message \
    slim/slim \
    guzzlehttp/guzzle

COPY gateway.php /var/www/html/

RUN a2enmod rewrite

EXPOSE 80