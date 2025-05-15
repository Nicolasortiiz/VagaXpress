FROM php:8.2-cli

WORKDIR /var/www

# Dependências do sistema
RUN apt-get update && apt-get install -y \
    gnupg2 unzip git libgpgme-dev libgpg-error-dev libassuan-dev dos2unix \
    libgcrypt20-dev libxml2-dev libssl-dev \
    && pecl install gnupg \
    && docker-php-ext-enable gnupg

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www

# Criação da chave GPG
RUN echo "Key-Type: RSA\n\
Key-Length: 2048\n\
Subkey-Type: RSA\n\
Subkey-Length: 2048\n\
Name-Real: Nicolas Ortiz\n\
Name-Email: nicolas.ortiz@pucpr.edu.br\n\
Expire-Date: 0\n\
Passphrase: senha@gpg" > keygen && \
    gpg --batch --generate-key keygen && \
    rm keygen

# Instalando dependências do PHP via Composer
COPY composer.json composer.lock ./
RUN composer install

# Copia todos os arquivos do host (serão sobrescritos se for montado como volume)


COPY entrypoint.sh /var/www/entrypoint.sh
RUN dos2unix entrypoint.sh
RUN chmod +x /var/www/entrypoint.sh

EXPOSE 8080 8001

CMD ["/var/www/entrypoint.sh"]

