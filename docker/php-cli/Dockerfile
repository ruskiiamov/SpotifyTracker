FROM php:${php_version}-cli

RUN apt-get update && apt-get install -y \
    cron \
    supervisor \
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

COPY cron-schedule /etc/cron.d/cron-schedule

RUN chmod 0644 /etc/cron.d/cron-schedule
RUN crontab /etc/cron.d/cron-schedule

COPY queue-worker.conf /etc/supervisor/conf.d/queue-worker.conf

RUN supervisorctl reread
RUN supervisorctl update

WORKDIR /var/www/html/

USER www-data:www-data

CMD supervisorctl start laravel-worker:*
