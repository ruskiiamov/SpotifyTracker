ARG composer_version
ARG php_version

FROM composer:${composer_version} as vendor

FROM php:${php_version}-fpm

COPY --from=vendor /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libonig-dev \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    zlib1g-dev \
    libzip-dev \
    unzip \
    libbz2-dev \
    libldap2-dev \
    libxslt-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd \
	&& docker-php-ext-install bcmath \
	&& docker-php-ext-install pdo_mysql \
	&& docker-php-ext-install pdo_pgsql \
	&& docker-php-ext-install soap \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html/

USER www-data:www-data

CMD composer install
